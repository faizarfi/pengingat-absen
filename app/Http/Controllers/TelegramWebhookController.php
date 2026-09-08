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
use Illuminate\Support\Facades\Cache;
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
        // ── 0. Tangani Callback Query (Tombol Inline / Checkbox) ──
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query'], $telegram, $wa);
            return;
        }

        $message = $update['message'] ?? null;
        if (!$message || !isset($message['text'])) {
            return;
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim($message['text']);
        $adminChatIdRaw = (string) config('telegram.admin_chat_id', '');
        $adminChatIds = array_filter(array_map('trim', explode(',', $adminChatIdRaw)));

        // Security check: jika admin_chat_id diset, hanya izinkan chat dari admin terdaftar
        if (!empty($adminChatIds) && !in_array($chatId, $adminChatIds, true)) {
            $telegram->sendMessage(
                $chatId,
                "⛔ <b>Akses Ditolak!</b>\nAnda tidak memiliki izin untuk mengontrol sistem Pengingat Absen.\nID Telegram Anda: <code>{$chatId}</code>\n\n<i>Silakan masukkan ID ini ke TELEGRAM_ADMIN_CHAT_ID di .env agar mendapatkan akses.</i>"
            );
            return;
        }

        // Cek jika sedang menunggu pesan broadcast setelah memilih pegawai lewat inline
        $awaitingBroadcast = Cache::get("telegram_awaiting_broadcast_{$chatId}");
        if (!empty($awaitingBroadcast) && !str_starts_with($text, '/')) {
            Cache::forget("telegram_awaiting_broadcast_{$chatId}");
            if (mb_strtolower($text, 'UTF-8') === 'batal') {
                $telegram->sendMessage($chatId, "❌ Pengiriman broadcast dibatalkan.");
                return;
            }
            $this->triggerBroadcast($chatId, $text, $telegram, $wa, $awaitingBroadcast);
            return;
        }

        $cmd = mb_strtolower($text, 'UTF-8');
        // Hapus mention bot jika ada (contoh: /masuk@bot_name -> /masuk)
        $cmd = preg_replace('/@\w+/', '', $cmd);

        // 1. Perintah Start / Bantuan / Menu
        if (in_array($cmd, ['/start', 'start', '/help', 'help', '/menu', 'menu', '❓ bantuan & perintah']) || str_contains($cmd, 'bantuan') || $cmd === 'menu') {
            $menu = "👋 <b>Halo Admin! Pusat Kontrol Pengingat Absen BPS</b>\n\n"
                  . "Silakan klik tombol menu di bawah atau ketik perintah:\n\n"
                  . "🎯 <b>/pilih</b> — Pilih pegawai tertentu lewat tombol checkbox interaktif\n"
                  . "📋 <b>/pegawai</b> — Daftar nama & nomor pegawai aktif\n"
                  . "🌅 <b>/masuk</b> — Kirim pengingat masuk ke SEMUA pegawai\n"
                  . "🌅 <b>/masuk [nama/id]</b> — Kirim pengingat masuk ke pegawai tertentu (cth: <code>/masuk faiz</code>)\n"
                  . "🌇 <b>/pulang</b> — Kirim pengingat pulang ke SEMUA pegawai\n"
                  . "🌇 <b>/pulang [nama/id]</b> — Kirim pengingat pulang ke pegawai tertentu (cth: <code>/pulang faiz</code>)\n"
                  . "📢 <b>/broadcast [pesan]</b> — Kirim pesan instan ke seluruh pegawai\n"
                  . "📊 <b>/status</b> — Cek status Agent & antrean pesan\n"
                  . "🏖️ <b>/libur</b> — Cek kalender hari libur & tanggal merah\n\n"
                  . "<i>Sistem siap melayani perintah remote dari HP Anda.</i>";

            $telegram->sendMessage($chatId, $menu, $telegram->getAdminMenuKeyboard());
            return;
        }

        // 2. Perintah Pilih Pegawai (Interactive Checkbox Selector)
        if (in_array($cmd, ['/pilih', 'pilih', '/select', 'select', 'pilih pegawai', '🎯 pilih pegawai']) || str_contains($cmd, 'pilih')) {
            $this->sendSelectorMessage($chatId, $telegram);
            return;
        }

        // 3. Perintah Daftar Pegawai
        if (in_array($cmd, ['/pegawai', 'pegawai', '/list', 'list', 'daftar', 'daftar pegawai', '📋 daftar pegawai', '/daftar']) || str_contains($cmd, 'pegawai') || str_contains($cmd, 'daftar')) {
            $this->sendEmployeeList($chatId, $telegram);
            return;
        }

        // 4. Perintah Kirim Masuk (bisa: /masuk [nama], /masuk, masuk [nama], masuk)
        if (str_starts_with($cmd, '/masuk ') || str_starts_with($cmd, 'masuk ')) {
            $param = trim(substr($text, strpos($text, ' ')));
            $matched = $this->findEmployeesByNameOrId($param);
            if ($matched->isEmpty()) {
                $telegram->sendMessage($chatId, "⚠️ Pegawai dengan nama/nomor <b>{$param}</b> tidak ditemukan. Ketik <b>/pegawai</b> untuk melihat daftar nama.");
                return;
            }
            $this->triggerPreCheckin($chatId, $telegram, $wa, $matched->pluck('id')->all());
            return;
        }

        if (in_array($cmd, ['/masuk', 'masuk', 'pagi', 'kirim pagi', '🌅 kirim masuk', '🌅 kirim masuk semua']) || $cmd === 'kirim masuk') {
            $this->triggerPreCheckin($chatId, $telegram, $wa);
            return;
        }

        // 5. Perintah Kirim Pulang (bisa: /pulang [nama], /pulang, pulang [nama], pulang)
        if (str_starts_with($cmd, '/pulang ') || str_starts_with($cmd, 'pulang ')) {
            $param = trim(substr($text, strpos($text, ' ')));
            $matched = $this->findEmployeesByNameOrId($param);
            if ($matched->isEmpty()) {
                $telegram->sendMessage($chatId, "⚠️ Pegawai dengan nama/nomor <b>{$param}</b> tidak ditemukan. Ketik <b>/pegawai</b> untuk melihat daftar nama.");
                return;
            }
            $this->triggerPreCheckout($chatId, $telegram, $wa, $matched->pluck('id')->all());
            return;
        }

        if (in_array($cmd, ['/pulang', 'pulang', 'sore', 'kirim sore', '🌇 kirim pulang', '🌇 kirim pulang semua']) || $cmd === 'kirim pulang') {
            $this->triggerPreCheckout($chatId, $telegram, $wa);
            return;
        }

        // 6. Perintah Status Sistem
        if (in_array($cmd, ['/status', 'status', 'cek', 'info', '📊 status sistem']) || str_contains($cmd, 'status')) {
            $this->sendStatusReport($chatId, $telegram);
            return;
        }

        // 7. Perintah Cek Hari Libur
        if (in_array($cmd, ['/libur', 'libur', 'kalender', '🏖️ cek hari libur']) || str_contains($cmd, 'libur')) {
            $this->sendHolidayReport($chatId, $telegram, $holiday);
            return;
        }

        // 8. Perintah Broadcast Kustom
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

    // ── Handler Callback Query (Inline Buttons / Checkbox) ──

    public function handleCallbackQuery(array $cb, TelegramService $telegram, WhatsAppService $wa): void
    {
        $callbackId = $cb['id'] ?? '';
        $data = $cb['data'] ?? '';
        $chatId = (string) ($cb['message']['chat']['id'] ?? '');
        $messageId = (int) ($cb['message']['message_id'] ?? 0);

        if (empty($chatId)) return;

        // Security check
        $adminChatIdRaw = (string) config('telegram.admin_chat_id', '');
        $adminChatIds = array_filter(array_map('trim', explode(',', $adminChatIdRaw)));
        if (!empty($adminChatIds) && !in_array($chatId, $adminChatIds, true)) {
            $telegram->answerCallbackQuery($callbackId, 'Akses ditolak', true);
            return;
        }

        $cacheKey = "telegram_sel_{$chatId}";
        $selectedIds = Cache::get($cacheKey, []);

        if (str_starts_with($data, 'tg_toggle:')) {
            $empId = (int) substr($data, 10);
            if (in_array($empId, $selectedIds)) {
                $selectedIds = array_values(array_diff($selectedIds, [$empId]));
            } else {
                $selectedIds[] = $empId;
            }
            Cache::put($cacheKey, $selectedIds, now()->addHours(2));
            $telegram->answerCallbackQuery($callbackId, 'Pilihan diperbarui');
            $this->updateSelectorMessage($chatId, $messageId, $selectedIds, $telegram);
            return;
        }

        if ($data === 'tg_all') {
            $selectedIds = Employee::where('is_active', '=', true, 'and')->pluck('id')->all();
            Cache::put($cacheKey, $selectedIds, now()->addHours(2));
            $telegram->answerCallbackQuery($callbackId, 'Semua pegawai dipilih');
            $this->updateSelectorMessage($chatId, $messageId, $selectedIds, $telegram);
            return;
        }

        if ($data === 'tg_reset') {
            $selectedIds = [];
            Cache::forget($cacheKey);
            $telegram->answerCallbackQuery($callbackId, 'Pilihan direset');
            $this->updateSelectorMessage($chatId, $messageId, $selectedIds, $telegram);
            return;
        }

        if ($data === 'tg_close') {
            $telegram->answerCallbackQuery($callbackId, 'Menu ditutup');
            $telegram->editMessageText($chatId, $messageId, "<i>Menu pemilihan pegawai telah ditutup. Ketik /pilih untuk membuka kembali.</i>");
            return;
        }

        if ($data === 'tg_action:masuk') {
            if (empty($selectedIds)) {
                $telegram->answerCallbackQuery($callbackId, '⚠️ Pilih minimal 1 pegawai terlebih dahulu!', true);
                return;
            }
            $telegram->answerCallbackQuery($callbackId, 'Memproses pengingat masuk...');
            $this->triggerPreCheckin($chatId, $telegram, $wa, $selectedIds);
            return;
        }

        if ($data === 'tg_action:pulang') {
            if (empty($selectedIds)) {
                $telegram->answerCallbackQuery($callbackId, '⚠️ Pilih minimal 1 pegawai terlebih dahulu!', true);
                return;
            }
            $telegram->answerCallbackQuery($callbackId, 'Memproses pengingat pulang...');
            $this->triggerPreCheckout($chatId, $telegram, $wa, $selectedIds);
            return;
        }

        if ($data === 'tg_action:broadcast') {
            if (empty($selectedIds)) {
                $telegram->answerCallbackQuery($callbackId, '⚠️ Pilih minimal 1 pegawai terlebih dahulu!', true);
                return;
            }
            Cache::put("telegram_awaiting_broadcast_{$chatId}", $selectedIds, now()->addMinutes(10));
            $telegram->answerCallbackQuery($callbackId);
            $count = count($selectedIds);
            $telegram->sendMessage(
                $chatId,
                "✍️ <b>Kirim Broadcast ke {$count} Pegawai Terpilih:</b>\n\nSilakan ketik isi pesan Anda di chat ini sekarang.\n<i>(Ketik 'batal' jika ingin membatalkan)</i>"
            );
            return;
        }

        $telegram->answerCallbackQuery($callbackId);
    }

    public function sendSelectorMessage(string $chatId, TelegramService $telegram): void
    {
        $cacheKey = "telegram_sel_{$chatId}";
        $selectedIds = Cache::get($cacheKey, []);
        $markup = $this->buildSelectorKeyboard($selectedIds);
        $text = $this->buildSelectorText($selectedIds);

        $telegram->sendMessage($chatId, $text, $markup);
    }

    public function updateSelectorMessage(string $chatId, int $messageId, array $selectedIds, TelegramService $telegram): void
    {
        $markup = $this->buildSelectorKeyboard($selectedIds);
        $text = $this->buildSelectorText($selectedIds);

        $telegram->editMessageText($chatId, $messageId, $text, $markup);
    }

    private function buildSelectorText(array $selectedIds): string
    {
        $total = Employee::where('is_active', '=', true, 'and')->count();
        $count = count($selectedIds);

        return "🎯 <b>Pilih Pegawai Pengingat Absen</b>\n\n"
             . "Klik tombol nama pegawai di bawah untuk memilih (☑️) atau batal (⬜).\n"
             . "Setelah dipilih, tekan tombol aksi pengiriman di bawah.\n\n"
             . "👥 <b>Terpilih:</b> {$count} dari {$total} pegawai";
    }

    private function buildSelectorKeyboard(array $selectedIds): array
    {
        $employees = Employee::where('is_active', '=', true, 'and')->orderBy('name')->get();
        $keyboard = [];
        $row = [];

        foreach ($employees as $emp) {
            $isChecked = in_array($emp->id, $selectedIds);
            $icon = $isChecked ? '☑️' : '⬜';
            $row[] = [
                'text'          => "{$icon} {$emp->name}",
                'callback_data' => "tg_toggle:{$emp->id}",
            ];

            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $count = count($selectedIds);
        $keyboard[] = [
            ['text' => "🌅 Masuk ({$count})", 'callback_data' => 'tg_action:masuk'],
            ['text' => "🌇 Pulang ({$count})", 'callback_data' => 'tg_action:pulang'],
        ];
        $keyboard[] = [
            ['text' => "📢 Broadcast ({$count})", 'callback_data' => 'tg_action:broadcast'],
        ];
        $keyboard[] = [
            ['text' => '✅ Pilih Semua', 'callback_data' => 'tg_all'],
            ['text' => '🔄 Reset Pilihan', 'callback_data' => 'tg_reset'],
        ];
        $keyboard[] = [
            ['text' => '❌ Tutup Menu', 'callback_data' => 'tg_close'],
        ];

        return ['inline_keyboard' => $keyboard];
    }

    private function sendEmployeeList(string $chatId, TelegramService $telegram): void
    {
        $employees = Employee::where('is_active', '=', true, 'and')->orderBy('name')->get();
        if ($employees->isEmpty()) {
            $telegram->sendMessage($chatId, "⚠️ Belum ada pegawai aktif di database.");
            return;
        }

        $msg = "📋 <b>DAFTAR PEGAWAI AKTIF (" . $employees->count() . " Orang)</b>\n\n";
        foreach ($employees as $idx => $emp) {
            $no = $idx + 1;
            $msg .= "{$no}. <b>{$emp->name}</b> (ID: <code>{$emp->id}</code>)\n   📱 {$emp->phone_number}\n";
        }
        $msg .= "\n💡 <i>Tips: Ketik <b>/masuk [nama/id]</b> atau <b>/pulang [nama/id]</b> untuk kirim cepat ke perorangan.</i>";

        $telegram->sendMessage($chatId, $msg);
    }

    private function findEmployeesByNameOrId(string $query)
    {
        return Employee::where('is_active', '=', true, 'and')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('id', $query)
                  ->orWhere('phone_number', 'LIKE', "%{$query}%");
            })
            ->get();
    }

    private function triggerPreCheckin(string $chatId, TelegramService $telegram, WhatsAppService $wa, ?array $employeeIds = null): void
    {
        $query = Employee::where('is_active', '=', true, 'and');
        if (!empty($employeeIds)) {
            $query->whereIn('id', $employeeIds);
        }
        $employees = $query->get();

        if ($employees->isEmpty()) {
            $telegram->sendMessage($chatId, "⚠️ Tidak ada pegawai yang dapat dikirimi pesan.");
            return;
        }

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

        $namesList = $employees->map(fn($e) => "• {$e->name}")->implode("\n");
        $targetText = !empty($employeeIds)
            ? "👥 <b>Penerima Terpilih ({$queued} Orang):</b>\n{$namesList}\n\n"
            : "👥 <b>Total Pegawai:</b> {$queued} / {$employees->count()} Orang\n\n";

        $telegram->sendMessage(
            $chatId,
            "✅ <b>Pengingat Masuk Berhasil Dimasukkan ke Antrean!</b>\n\n"
            . $targetText
            . "⏰ <b>Target Masuk:</b> {$checkIn} WIB (Sisa ~{$minutesLeftGlobal} menit)\n"
            . "💻 WhatsApp Agent PC kantor akan segera memproses pengiriman dengan jeda aman anti-ban."
        );
    }

    private function triggerPreCheckout(string $chatId, TelegramService $telegram, WhatsAppService $wa, ?array $employeeIds = null): void
    {
        $query = Employee::where('is_active', '=', true, 'and');
        if (!empty($employeeIds)) {
            $query->whereIn('id', $employeeIds);
        }
        $employees = $query->get();

        if ($employees->isEmpty()) {
            $telegram->sendMessage($chatId, "⚠️ Tidak ada pegawai yang dapat dikirimi pesan.");
            return;
        }

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

        $namesList = $employees->map(fn($e) => "• {$e->name}")->implode("\n");
        $targetText = !empty($employeeIds)
            ? "👥 <b>Penerima Terpilih ({$queued} Orang):</b>\n{$namesList}\n\n"
            : "👥 <b>Total Pegawai:</b> {$queued} / {$employees->count()} Orang\n\n";

        $telegram->sendMessage(
            $chatId,
            "✅ <b>Pengingat Pulang Berhasil Dimasukkan ke Antrean!</b>\n\n"
            . $targetText
            . "⏰ <b>Target Pulang:</b> {$checkOut} WIB (Sisa ~{$minutesLeftGlobal} menit)\n"
            . "💻 WhatsApp Agent PC kantor akan segera memproses pengiriman dengan jeda aman anti-ban."
        );
    }

    private function triggerBroadcast(string $chatId, string $customMessage, TelegramService $telegram, WhatsAppService $wa, ?array $employeeIds = null): void
    {
        $query = Employee::where('is_active', '=', true, 'and');
        if (!empty($employeeIds)) {
            $query->whereIn('id', $employeeIds);
        }
        $employees = $query->get();

        if ($employees->isEmpty()) {
            $telegram->sendMessage($chatId, "⚠️ Tidak ada pegawai yang dapat dikirimi broadcast.");
            return;
        }

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

        $namesList = $employees->map(fn($e) => "• {$e->name}")->implode("\n");
        $targetText = !empty($employeeIds)
            ? "👥 <b>Penerima Terpilih ({$queued} Orang):</b>\n{$namesList}\n\n"
            : "👥 <b>Penerima:</b> {$queued} Orang\n\n";

        $telegram->sendMessage(
            $chatId,
            "📢 <b>Broadcast Berhasil Dimasukkan ke Antrean!</b>\n\n"
            . $targetText
            . "💬 <b>Isi Pesan:</b>\n<i>\"{$customMessage}\"</i>"
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
        $totalActive = Employee::where('is_active', '=', true, 'and')->count();

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
