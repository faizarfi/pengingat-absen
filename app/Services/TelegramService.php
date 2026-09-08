<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $baseUrl;
    protected ?string $adminChatId;

    public function __construct()
    {
        $this->token = config('telegram.bot_token', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
        $this->adminChatId = config('telegram.admin_chat_id', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('TelegramService: Token belum dikonfigurasi.');
            return false;
        }

        try {
            $payload = [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post("{$this->baseUrl}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::error('TelegramService API error: ' . $response->status() . ' - ' . $response->body());
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('TelegramService: Gagal mengirim pesan', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendAdmin(string $text, ?array $replyMarkup = null): bool
    {
        if (empty($this->adminChatId)) {
            Log::warning('TelegramService: TELEGRAM_ADMIN_CHAT_ID belum diisi.');
            return false;
        }

        return $this->sendMessage($this->adminChatId, $text, $replyMarkup ?? $this->getAdminMenuKeyboard());
    }

    public function setWebhook(string $url): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post("{$this->baseUrl}/setWebhook", ['url' => $url]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('TelegramService: Gagal set webhook', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post("{$this->baseUrl}/deleteWebhook", [
                    'drop_pending_updates' => $dropPendingUpdates,
                ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('TelegramService: Gagal delete webhook', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getUpdates(int $offset = 0, int $timeout = 20): array
    {
        if (!$this->isConfigured()) return [];

        try {
            $response = Http::withoutVerifying()
                ->timeout($timeout + 5)
                ->get("{$this->baseUrl}/getUpdates", [
                    'offset'  => $offset,
                    'timeout' => $timeout,
                ]);

            if ($response->successful()) {
                return $response->json()['result'] ?? [];
            }
        } catch (\Exception $e) {
            // timeout normal pada long-polling
        }

        return [];
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $payload = ['callback_query_id' => $callbackQueryId];
            if (!empty($text)) {
                $payload['text'] = $text;
                $payload['show_alert'] = $showAlert;
            }

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post("{$this->baseUrl}/answerCallbackQuery", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('TelegramService: Gagal answerCallbackQuery', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function editMessageText(string $chatId, int $messageId, string $text, ?array $replyMarkup = null): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $payload = [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post("{$this->baseUrl}/editMessageText", $payload);

            if (!$response->successful()) {
                Log::error('TelegramService editMessageText error: ' . $response->status() . ' - ' . $response->body());
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('TelegramService: Gagal editMessageText', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getAdminMenuKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '🎯 Pilih Pegawai'],
                    ['text' => '📋 Daftar Pegawai'],
                ],
                [
                    ['text' => '🌅 Kirim Masuk'],
                    ['text' => '🌇 Kirim Pulang'],
                ],
                [
                    ['text' => '📊 Status Sistem'],
                    ['text' => '🏖️ Cek Hari Libur'],
                ],
                [
                    ['text' => '❓ Bantuan & Perintah'],
                ]
            ],
            'resize_keyboard' => true,
            'persistent'      => true,
        ];
    }
}
