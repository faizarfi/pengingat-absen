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
