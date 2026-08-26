<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\WaAgentHeartbeat;
use App\Models\WaOutbox;
use App\Services\HolidayService;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramService $telegram, WhatsAppService $wa, HolidayService $holiday)
    {
        $update = $request->all();
        $this->processUpdate($update, $telegram, $wa, $holiday);

        return response()->json(['status' => 'ok']);
    }

    public function processUpdate(array $update, TelegramService $telegram, WhatsAppService $wa, HolidayService $holiday): void
    {
        $message = $update['message'] ?? null;
        if (!$message || !isset($message['text'])) {
            return;
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim($message['text']);
        $adminChatId = (string) config('telegram.admin_chat_id', '');

        // Security check: jika admin_chat_id diset, hanya izinkan chat dari admin
        if (!empty($adminChatId) && $chatId !== $adminChatId) {
            $telegram->sendMessage(
                $chatId,
                "⛔ <b>Akses Ditolak!</b>\nAnda tidak memiliki izin untuk mengontrol sistem Pengingat Absen.\nID Telegram Anda: <code>{$chatId}</code>"
            );
            return;
        }

        $cmd = strtolower($text);

        // 1. Perintah Start / Bantuan / Menu
        if ($cmd === '/start' || $cmd === '/help' || str_contains($cmd, 'bantuan')) {
            $menu = "👋 <b>Halo Admin! Pusat Kontrol Pengingat Absen BPS</b>\n\n"
                  . "Silakan klik tombol menu di bawah atau ketik perintah:\n\n"
                  . "🌅 <b>/masuk</b> — Kirim pengingat absen masuk pagi\n"
                  . "🌇 <b>/pulang</b> — Kirim pengingat absen pulang sore\n"
                  . "📊 <b>/status</b> — Cek status Agent & antrean pesan\n"
                  . "🏖️ <b>/libur</b> — Cek kalender hari libur & tanggal merah\n"
                  . "📢 <b>/broadcast [pesan]</b> — Kirim pesan instan ke seluruh pegawai\n\n"
                  . "<i>Sistem siap melayani perintah remote dari HP Anda.</i>";

            $telegram->sendMessage($chatId, $menu, $telegram->getAdminMenuKeyboard());
            return;
        }

        // 2. Perintah Kirim Masuk
        if ($cmd === '/masuk' || str_contains($cmd, 'kirim masuk')) {
            $this->triggerPreCheckin($chatId, $telegram, $wa);
            return;
        }

        // 3. Perintah Kirim Pulang
        if ($cmd === '/pulang' || str_contains($cmd, 'kirim pulang')) {
            $this->triggerPreCheckout($chatId, $telegram, $wa);
            return;
        }

        // 4. Perintah Status Sistem
        if ($cmd === '/status' || str_contains($cmd, 'status')) {
            $this->sendStatusReport($chatId, $telegram);
            return;
        }

        // 5. Perintah Cek Hari Libur
        if ($cmd === '/libur' || str_contains($cmd, 'libur')) {
            $this->sendHolidayReport($chatId, $telegram, $holiday);
            return;
        }

        // 6. Perintah Broadcast Kustom
        if (str_starts_with($cmd, '/broadcast ') || str_starts_with($cmd, 'broadcast ')) {
            $customText = trim(substr($text, strpos($text, ' ')));
            $this->triggerBroadcast($chatId, $customText, $telegram, $wa);
            return;
        }

        // Respon tidak dikenal
        $telegram->sendMessage(
            $chatId,
            "❓ Perintah <code>{$text}</code> tidak dikenali. Ketik <b>/help</b> atau klik tombol menu di bawah.",
            $telegram->getAdminMenuKeyboard()
        );
    }

    private function triggerPreCheckin(string $chatId, TelegramService $telegram, WhatsAppService $wa): void
    {
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $template = Setting::get('template_pre_checkin', "{name},\n\nIni adalah pengingat absen masuk.\nJam masuk kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon segera lakukan absen masuk. Jangan lupa absen ya!\n\nTerima kasih atas perhatian Anda.\n\nHormat kami,\n{organization}");
        $checkIn = Setting::get('check_in_time', '07:30');
        $target = Carbon::createFromFormat('H:i', $checkIn);

        $nowGlobal = Carbon::now();
        $targetTodayGlobal = (clone $target)->setDate($nowGlobal->year, $nowGlobal->month, $nowGlobal->day);
        $minutesLeftGlobal = (int) max(0, ceil(($targetTodayGlobal->getTimestamp() - $nowGlobal->getTimestamp()) / 60));

        $queued = 0;
        foreach ($employees as $emp) {
            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);
            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), Setting::get('organization_name', 'BPS Kabupaten Karanganyar')],
                $template
            );

            // Pantun acak
            $pantun = DB::table('pantuns')->where('type', 'masuk')->inRandomOrder()->value('text');
            if ($pantun) {
                $pantun = str_replace('\\n', PHP_EOL, $pantun);
                $lines = preg_split("/\r\n|\n|\r/", $text);
                if (isset($lines[0]) && trim($lines[0]) !== '') {
                    array_splice($lines, 1, 0, ['', $pantun]);
                    $text = implode(PHP_EOL, $lines);
                }
            }

            try {
                $wa->send($emp->id, $emp->phone_number, $text, 'pre_checkin');
                $queued++;
            } catch (\Exception $e) {
                Log::error('Telegram triggerPreCheckin failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        $telegram->sendMessage(
            $chatId,
            "✅ <b>Pengingat Masuk Berhasil Dimasukkan ke Antrean!</b>\n\n"
            . "👥 Total Pegawai : <b>{$queued} / {$employees->count()} Orang</b>\n"
            . "⏰ Target Masuk  : <b>{$checkIn} WIB</b> (Sisa ~{$minutesLeftGlobal} menit)\n"
            . "💻 WhatsApp Desktop PC kantor akan otomatis memproses pengiriman dengan jeda aman anti-ban."
        );
    }

    private function triggerPreCheckout(string $chatId, TelegramService $telegram, WhatsAppService $wa): void
    {
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $template = Setting::get('template_pre_checkout', "{name},\n\nIni adalah pengingat absen pulang.\nJam pulang kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon jangan lupa melakukan absen pulang sebelum meninggalkan kantor.\n\nTerima kasih atas dedikasi dan kerja keras Anda hari ini.\n\nHormat kami,\n{organization}");
        $isFriday = now()->isFriday();
        $checkOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');
        $target = Carbon::createFromFormat('H:i', $checkOut);

        $nowGlobal = Carbon::now();
        $targetTodayGlobal = (clone $target)->setDate($nowGlobal->year, $nowGlobal->month, $nowGlobal->day);
        $minutesLeftGlobal = (int) max(0, ceil(($targetTodayGlobal->getTimestamp() - $nowGlobal->getTimestamp()) / 60));

        $queued = 0;
        foreach ($employees as $emp) {
            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);
            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), Setting::get('organization_name', 'BPS Kabupaten Karanganyar')],
                $template
            );

            // Pantun acak
            $pantun = DB::table('pantuns')->where('type', 'pulang')->inRandomOrder()->value('text');
            if ($pantun) {
                $pantun = str_replace('\\n', PHP_EOL, $pantun);
                $lines = preg_split("/\r\n|\n|\r/", $text);
                if (isset($lines[0]) && trim($lines[0]) !== '') {
                    array_splice($lines, 1, 0, ['', $pantun]);
                    $text = implode(PHP_EOL, $lines);
                }
            }

            try {
                $wa->send($emp->id, $emp->phone_number, $text, 'pre_checkout');
                $queued++;
            } catch (\Exception $e) {
                Log::error('Telegram triggerPreCheckout failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        $telegram->sendMessage(
            $chatId,
            "✅ <b>Pengingat Pulang Berhasil Dimasukkan ke Antrean!</b>\n\n"
            . "👥 Total Pegawai : <b>{$queued} / {$employees->count()} Orang</b>\n"
            . "⏰ Target Pulang : <b>{$checkOut} WIB</b> (Sisa ~{$minutesLeftGlobal} menit)\n"
            . "💻 WhatsApp Desktop PC kantor akan otomatis memproses pengiriman dengan jeda aman anti-ban."
        );
    }

    private function triggerBroadcast(string $chatId, string $customMessage, TelegramService $telegram, WhatsAppService $wa): void
    {
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $orgName = Setting::get('organization_name', 'BPS Kabupaten Karanganyar');

        $queued = 0;
        foreach ($employees as $emp) {
            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{organization}'],
                [$namaLengkap, $kata, $orgName],
                $customMessage
            );

            try {
                $wa->send($emp->id, $emp->phone_number, $text, 'manual');
                $queued++;
            } catch (\Exception $e) {
                Log::error('Telegram broadcast failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        $telegram->sendMessage(
            $chatId,
            "📢 <b>Broadcast Instan Dimasukkan ke Antrean!</b>\n\n"
            . "👥 Penerima : <b>{$queued} Orang</b>\n"
            . "💬 Pesan : \n<i>\"{$customMessage}\"</i>"
        );
    }

    private function sendStatusReport(string $chatId, TelegramService $telegram): void
    {
        $agent = WaAgentHeartbeat::where('agent_name', 'default')->first();
        $isOnline = $agent && $agent->isOnline();
        $waReady = $agent && $agent->whatsapp_ready;

        $pending = WaOutbox::pending()->count();
        $processing = WaOutbox::processing()->count();
        $sentToday = WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count();
        $failed = WaOutbox::failed()->today()->count();
        $totalActive = Employee::where('is_active', true)->count();

        $agentIcon = $isOnline ? '🟢 Online' : '🔴 Offline';
        $waIcon = $waReady ? '✅ Ready' : '⚠️ Standby';

        $report = "📊 <b>STATUS SISTEM PENGINGAT ABSEN</b>\n\n"
                . "🖥️ <b>Agent PC Kantor :</b> {$agentIcon}\n"
                . "💬 <b>WhatsApp Desktop :</b> {$waIcon}\n"
                . "👥 <b>Pegawai Aktif     :</b> {$totalActive} Orang\n\n"
                . "<b>Statistik Outbox Hari Ini:</b>\n"
                . "• ⏳ Pending    : <b>{$pending}</b>\n"
                . "• ⚙️ Diproses   : <b>{$processing}</b>\n"
                . "• ✅ Terkirim   : <b>{$sentToday}</b>\n"
                . "• ❌ Gagal      : <b>{$failed}</b>";

        $telegram->sendMessage($chatId, $report);
    }

    private function sendHolidayReport(string $chatId, TelegramService $telegram, HolidayService $holidayService): void
    {
        $isTodayHoliday = $holidayService->isHoliday(now());
        $todayInfo = $holidayService->getHolidayInfo(now());
        $holidayName = $todayInfo ? $todayInfo->name : (now()->isWeekend() ? 'Akhir Pekan (' . (now()->isSaturday() ? 'Sabtu' : 'Minggu') . ')' : null);

        $upcoming = \App\Models\Holiday::where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->limit(4)
            ->get();

        $upcomingText = '';
        foreach ($upcoming as $h) {
            $formattedDate = Carbon::parse($h->date)->translatedFormat('d M Y');
            $upcomingText .= "• <b>{$formattedDate}</b> : {$h->name}\n";
        }

        $statusToday = $isTodayHoliday 
            ? "🏖️ <b>Hari Ini LIBUR:</b> {$holidayName}\n<i>(Scheduler otomatis tidak mengirim pengingat)</i>" 
            : "💼 <b>Hari Ini: HARI KERJA AKTIF</b>\n<i>(Scheduler berjalan normal)</i>";

        $report = "📅 <b>KALENDER HARI LIBUR & KERJA</b>\n\n"
                . "{$statusToday}\n\n"
                . "<b>Libur Nasional Mendatang:</b>\n"
                . ($upcomingText ?: "<i>Tidak ada jadwal libur terdekat.</i>");

        $telegram->sendMessage($chatId, $report);
    }
}
