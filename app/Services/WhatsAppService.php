<?php

namespace App\Services;

use App\Models\WaOutbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Router method — pilih driver berdasarkan config.
     *
     * Jika WA_DRIVER=desktop, pesan dimasukkan ke wa_outbox (diproses oleh WaDesktopAgent).
     * Jika WA_DRIVER=api, pesan dikirim langsung via HTTP ke provider (Fonnte/legacy).
     */
    public function send(int $employeeId, string $phone, string $message, string $type = 'manual'): array
    {
        $driver = config('whatsapp.driver', 'desktop');

        if ($driver === 'desktop') {
            $outbox = $this->queueMessage($employeeId, $phone, $message, $type);
            return [
                'success'   => true,
                'driver'    => 'desktop',
                'outbox_id' => $outbox->id,
                'status'    => 'queued',
            ];
        }

        // Legacy: kirim langsung via API provider
        $result = $this->sendMessage($phone, $message);
        $result['driver'] = 'api';
        return $result;
    }

    /**
     * Masukkan pesan ke wa_outbox untuk diproses oleh WA Desktop Agent.
     */
    public function queueMessage(
        int $employeeId,
        string $phone,
        string $message,
        string $type = 'manual',
        ?\DateTimeInterface $scheduledAt = null
    ): WaOutbox {
        $phone = $this->normalizePhone($phone);

        // Tandai bahwa ada batch pengiriman aktif
        if (!\Illuminate\Support\Facades\Cache::has('wa_batch_active')) {
            \Illuminate\Support\Facades\Cache::put('wa_batch_active', true, 7200);
            \Illuminate\Support\Facades\Cache::put('wa_batch_start', now()->timestamp, 7200);
        }

        return WaOutbox::create([
            'employee_id'  => $employeeId,
            'phone_number' => $phone,
            'message'      => $message,
            'type'         => $type,
            'status'       => WaOutbox::STATUS_PENDING,
            'attempts'     => 0,
            'scheduled_at' => $scheduledAt ?? now(),
        ]);
    }

    /**
     * Periksa apakah antrean outbox telah selesai diproses semua,
     * dan kirimkan ringkasan rekap ke Telegram Admin jika iya.
     */
    public function checkAndSendBatchCompletionReport(): bool
    {
        if (!\Illuminate\Support\Facades\Cache::get('wa_batch_active')) {
            return false;
        }

        $pendingCount = WaOutbox::whereIn('status', [
            WaOutbox::STATUS_PENDING,
            WaOutbox::STATUS_PROCESSING,
            WaOutbox::STATUS_RETRY
        ])->count();

        if ($pendingCount > 0) {
            return false;
        }

        // Antrean telah selesai diproses semua!
        \Illuminate\Support\Facades\Cache::forget('wa_batch_active');
        $startTime = \Illuminate\Support\Facades\Cache::pull('wa_batch_start', now()->subMinutes(3)->timestamp);
        $durationSeconds = max(1, now()->timestamp - $startTime);
        $durationText = $durationSeconds >= 60 
            ? floor($durationSeconds / 60) . " Menit " . ($durationSeconds % 60) . " Detik"
            : "{$durationSeconds} Detik";

        $sentToday = WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count();
        $failedToday = WaOutbox::failed()->today()->with('employee')->get();
        $failedCount = $failedToday->count();

        $failedDetails = '';
        if ($failedCount > 0) {
            $failedDetails = "\n\n❌ <b>Daftar Gagal Kirim:</b>";
            foreach ($failedToday->take(5) as $f) {
                $name = $f->employee ? $f->employee->name : $f->phone_number;
                $failedDetails .= "\n• <b>{$name}</b> ({$f->phone_number}): <i>{$f->last_error}</i>";
            }
            if ($failedCount > 5) {
                $failedDetails .= "\n<i>...dan " . ($failedCount - 5) . " pesan gagal lainnya.</i>";
            }
        }

        $report = "🎉 <b>LAPORAN PENGIRIMAN WHATSAPP SELESAI</b>\n\n"
                . "✅ <b>Berhasil Terkirim :</b> {$sentToday} Pesan\n"
                . "❌ <b>Gagal Dikirim     :</b> {$failedCount} Pesan\n"
                . "⏱️ <b>Durasi Pengiriman :</b> {$durationText}\n"
                . "💻 <b>Status Antrean    :</b> Selesai Diproses (0 pending)"
                . $failedDetails . "\n\n"
                . "💡 <i>Semua pesan telah dikirim oleh WhatsApp Desktop PC kantor secara aman.</i>";

        return app(TelegramService::class)->sendAdmin($report);
    }

    /**
     * Legacy: Kirim pesan langsung via HTTP ke provider (Fonnte / WaSender / Infobip).
     * Dipertahankan sebagai fallback selama masa transisi.
     */
    public function sendMessage(string $phone, string $message): array
    {
        $phone = $this->normalizePhone($phone);

        $base = config('whatsapp.url');
        $token = config('whatsapp.key');

        if (empty($base) || empty($token)) {
            return ['success' => false, 'error' => 'Missing WhatsApp config'];
        }

        // ensure scheme
        if (!preg_match('#^https?://#i', $base)) {
            $base = 'https://' . $base;
        }

        // Determine provider and build endpoint/payload
        $endpoint = rtrim($base, '/') . '/send'; // default for Fonnte
        $payload = ['target' => $phone, 'message' => $message];
        $headers = ['Content-Type' => 'application/json', 'Authorization' => $token];

        // If using WaSender API
        if (stripos($base, 'wasender') !== false) {
            $endpoint = rtrim($base, '/') . '/api/send-message';
            $payload = ['to' => $phone, 'text' => $message];
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        // If using Infobip
        elseif (stripos($base, 'infobip') !== false) {
            $endpoint = rtrim($base, '/') . '/whatsapp/1/message/text';
            $payload = [
                'from' => config('whatsapp.from'),
                'to' => '62' . $phone,
                'content' => ['type' => 'text', 'text' => $message],
            ];
            $headers['Authorization'] = 'App ' . $token;
        }
        // Fonnte is default above
        else {
            $headers['Authorization'] = $token;
        }

        try {
            Log::info('WhatsApp request', [
                'endpoint' => $endpoint,
                'payload' => $payload,
                'token' => substr($token, 0, 10) . '***'
            ]);

            $options = [];
            if (app()->environment('local') || config('app.debug')) {
                $options['verify'] = false;
                Log::warning('HTTP SSL verify disabled for WhatsAppService (local/debug mode)');
            }

            $resp = Http::withOptions($options)->withHeaders($headers)->post($endpoint, $payload);

            $responseData = $resp->json() ?? [];
            Log::info('WhatsApp service response', [
                'endpoint' => $endpoint,
                'status' => $resp->status(),
                'body' => $resp->body(),
                'decoded' => $responseData
            ]);

            // Handle rate limit (429) - don't retry
            if ($resp->status() === 429) {
                $retryAfter = $responseData['retry_after'] ?? 60;
                Log::warning('WhatsApp rate limited', ['retry_after' => $retryAfter, 'message' => $responseData['message'] ?? '']);
                return ['success' => false, 'error' => 'rate_limited', 'retry_after' => $retryAfter];
            }

            // Handle auth errors
            if ($resp->status() === 401) {
                Log::error('WhatsApp auth error', ['message' => $responseData['message'] ?? 'Unauthorized']);
                return ['success' => false, 'error' => 'auth_failed', 'status' => 401, 'message' => $responseData['message'] ?? 'Invalid API key'];
            }

            // For Fonnte API - check response format
            if (stripos($base, 'fonnte') !== false) {
                $isSuccess = $responseData['status'] ?? $responseData['success'] ?? false;
                Log::debug('Fonnte response check', ['status_code' => $resp->status(), 'api_success' => $isSuccess]);
                
                if ($resp->status() === 200 && $isSuccess) {
                    return ['success' => true, 'status' => $resp->status()];
                } elseif ($resp->status() === 200) {
                    return ['success' => false, 'error' => 'api_error', 'status' => 200, 'message' => $responseData['detail'] ?? 'API returned error'];
                }
            }

            return ['success' => $resp->successful(), 'status' => $resp->status()];
        } catch (\Exception $e) {
            Log::error('WhatsApp exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => 'exception', 'message' => $e->getMessage()];
        }
    }

    /**
     * Normalize phone number: hapus karakter non-digit, hapus leading 0.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }
        return $phone;
    }
}
