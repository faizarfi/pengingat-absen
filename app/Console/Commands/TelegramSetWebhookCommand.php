<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhookCommand extends Command
{
    protected $signature = 'telegram:set-webhook {url? : URL publik domain web Anda}';
    protected $description = 'Set webhook URL untuk Telegram Bot saat di-hosting di VPS/Domain publik';

    public function handle(TelegramService $telegram): int
    {
        $url = $this->argument('url') ?: url('/api/telegram/webhook');

        $this->info("Mendaftarkan webhook Telegram ke: {$url}");

        $success = $telegram->setWebhook($url);

        if ($success) {
            $this->info("✅ Webhook Telegram berhasil didaftarkan!");
        } else {
            $this->error("❌ Gagal mendaftarkan webhook. Pastikan TELEGRAM_BOT_TOKEN valid dan URL menggunakan HTTPS.");
        }

        return $success ? 0 : 1;
    }
}
