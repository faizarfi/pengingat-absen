<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\SendWhatsAppJob;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\WhatsAppService;

class WebhookController extends Controller
{
    public function handleFonnte(Request $request)
    {
        Log::info('Fonnte webhook received', $request->all());

        $sender = $request->input('sender', '');
        $rawMessage = trim($request->input('message', ''));
        $message = strtolower($rawMessage);
        $adminNumber = config('whatsapp.admin_number', '');

        if (empty($adminNumber) || $this->normalizePhone($sender) !== $this->normalizePhone($adminNumber)) {
            Log::warning('Webhook: Unauthorized sender or admin not configured', ['sender' => $sender]);
            return response()->json(['status' => 'ignored', 'message' => 'Unauthorized sender'], 200);
        }

        // 1. Command: tambah
        if (preg_match('/^tambah[\s\r\n]+(.+)$/is', $rawMessage, $matches)) {
            return $this->handleAddEmployee($matches[1]);
        }

        // 2. Command: masuk / pulang
        if (str_contains($message, 'masuk') || str_contains($message, 'pulang')) {
            $type = str_contains($message, 'masuk') ? 'masuk' : 'pulang';
            $result = $this->broadcastReminder($type);
            $this->sendAdminConfirmation("✅ Broadcast pengingat " . strtoupper($type) . " berhasil dikirim ke {$result['sent']}/{$result['total']} karyawan aktif.");
            return response()->json(['status' => 'ok', 'command' => $type, ...$result]);
        }

        // Help menu fallback
        $this->sendAdminConfirmation("📋 *PANDUAN PERINTAH BOT WA:*\n\n"
            . "1️⃣ *masuk*\n↳ Kirim pengingat absen masuk ke semua pegawai aktif.\n\n"
            . "2️⃣ *pulang*\n↳ Kirim pengingat absen pulang ke semua pegawai aktif.\n\n"
            . "3️⃣ *tambah [Nama] [Nomor]*\n↳ Daftarkan pegawai baru.\n_Contoh: tambah Budi Santoso 081234567890_");

        return response()->json(['status' => 'ignored', 'message' => 'Help message sent']);
    }

    private function handleAddEmployee(string $payload)
    {
        $lines = array_values(array_filter(preg_split("/\r\n|\n|\r/", trim($payload))));
        if (empty($lines)) {
            $this->sendAdminConfirmation("⚠️ *Format Pendaftaran Salah!*\n\nContoh:\n*tambah Budi Santoso 081234567890*");
            return response()->json(['status' => 'error', 'message' => 'Empty payload']);
        }

        $added = 0; $duplicates = 0; $invalid = 0; $addedNames = [];

        foreach ($lines as $line) {
            $parsed = $this->parseEmployeeLine($line);
            if (!$parsed) {
                $invalid++;
                continue;
            }

            [$name, $cleanPhone] = $parsed;

            if (Employee::whereIn('phone_number', [$cleanPhone, '0' . $cleanPhone])->exists()) {
                $duplicates++;
                continue;
            }

            $emp = Employee::create(['name' => $name, 'phone_number' => $cleanPhone, 'is_active' => true]);
            $added++;
            $addedNames[] = "• {$name} (0{$cleanPhone})";
        }

        // Single add response
        if (count($lines) === 1) {
            if ($added > 0) {
                $this->sendAdminConfirmation("✅ *Pegawai Berhasil Ditambahkan!*\n\n👤 Nama: *{$lines[0]}*\n📌 Status: Aktif\n\n_Otomatis akan menerima pengingat absen berikutnya._");
                return response()->json(['status' => 'ok', 'command' => 'tambah']);
            }
            if ($duplicates > 0) {
                $this->sendAdminConfirmation("⚠️ *Nomor Sudah Terdaftar!*");
                return response()->json(['status' => 'exists']);
            }
            $this->sendAdminConfirmation("⚠️ *Data Tidak Valid!*\nNama dan nomor HP harus diisi dengan benar.");
            return response()->json(['status' => 'error']);
        }

        // Bulk add summary response
        $msg = "📊 *REKAP PENDAFTARAN PEGAWAI (BULK)*\n\n✅ *Berhasil Ditambahkan:* {$added} orang\n"
            . ($duplicates > 0 ? "⚠️ *Dilewati (Sudah Ada):* {$duplicates} orang\n" : "")
            . ($invalid > 0 ? "❌ *Format Gagal:* {$invalid} baris\n" : "");

        if (!empty($addedNames)) {
            $msg .= "\n*Daftar Pegawai Baru:*\n" . implode("\n", array_slice($addedNames, 0, 5));
            if (count($addedNames) > 5) $msg .= "\n_...dan " . (count($addedNames) - 5) . " pegawai lainnya._";
        }

        $this->sendAdminConfirmation($msg);
        return response()->json(['status' => 'ok', 'added' => $added, 'duplicates' => $duplicates, 'invalid' => $invalid]);
    }

    private function parseEmployeeLine(string $line): ?array
    {
        $line = trim(preg_replace('/^\d+[\.\)]\s*|^[-*•]\s*/', '', $line));
        if (preg_match('/^(.*?)(?:[\s,#:\-]+)(\+?62[0-9\s\-]+|08[0-9\s\-]+|8[0-9\s\-]+)$/i', $line, $m)) {
            $name = trim($m[1]);
            $rawPhone = trim($m[2]);
        } else {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 2) return null;
            $rawPhone = array_pop($parts);
            $name = implode(' ', $parts);
        }

        $cleanPhone = $this->normalizePhone($rawPhone);
        return (empty($name) || strlen($cleanPhone) < 8) ? null : [$name, $cleanPhone];
    }

    private function broadcastReminder(string $type = 'masuk'): array
    {
        $isMasuk = $type === 'masuk';
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $orgName = Setting::get('organization_name', 'BPS Kabupaten Karanganyar');
        
        $template = $isMasuk
            ? Setting::get('template_pre_checkin', "{name},\n\nIni adalah pengingat absen masuk.\nJam masuk kerja: {target_time} WIB (tersisa {minutes_left} mnt).\n\nHormat kami,\n{organization}")
            : Setting::get('template_pre_checkout', "{name},\n\nIni adalah pengingat absen pulang.\nJam pulang: {target_time} WIB (tersisa {minutes_left} mnt).\n\nHormat kami,\n{organization}");
            
        $isFriday = Carbon::now()->isFriday();
        $defaultOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');
        $timeStr = Setting::get($isMasuk ? 'check_in_time' : ($isFriday ? 'check_out_time_friday' : 'check_out_time'), $isMasuk ? '07:30' : $defaultOut);
        $targetToday = Carbon::createFromTimeString($timeStr);
        $minutesLeft = (int) max(0, Carbon::now()->diffInMinutes($targetToday, false));

        // Get random pantun
        $pantun = DB::table('pantuns')->where('type', $type)->inRandomOrder()->value('text');
        $pantun = $pantun ? str_replace('\\n', PHP_EOL, $pantun) : null;
        $sent = 0;

        foreach ($employees as $emp) {
            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), $orgName],
                $template
            );

            if (!str_contains($template, '{minutes_left}')) {
                $text .= "\n\nTersisa waktu kurang lebih {$minutesLeft} menit.";
            }

            if ($pantun) {
                if (str_contains($text, '{pantun}')) {
                    $text = str_replace('{pantun}', $pantun, $text);
                } else {
                    $lines = preg_split("/\r\n|\n|\r/", $text);
                    if (isset($lines[0]) && trim($lines[0]) !== '') {
                        array_splice($lines, 1, 0, ['', $pantun]);
                        $text = implode(PHP_EOL, $lines);
                    } else {
                        $text .= PHP_EOL . PHP_EOL . $pantun;
                    }
                }
            }

            $logId = DB::table('wa_logs')->insertGetId([
                'employee_id'  => $emp->id,
                'type'         => "webhook_check{$type}",
                'scheduled_at' => now(),
                'status'       => 'pending',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            try {
                app(WhatsAppService::class)->send($emp->id, $emp->phone_number, $text, "webhook_check{$type}");
                $sent++;
            } catch (\Exception $e) {
                Log::error("Webhook broadcast {$type} failed", ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        return ['sent' => $sent, 'total' => $employees->count()];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return preg_replace('/^(62|0)/', '', $phone);
    }

    private function sendAdminConfirmation(string $message): void
    {
        try {
            $adminNumber = config('whatsapp.admin_number', '');
            if (!empty($adminNumber)) {
                app(WhatsAppService::class)->send(0, $adminNumber, $message, 'admin_confirmation');
                Log::info('Webhook: Admin confirmation queued to outbox');
            }
        } catch (\Exception $e) {
            Log::error('Webhook: Failed to send admin confirmation', ['error' => $e->getMessage()]);
        }
    }
}