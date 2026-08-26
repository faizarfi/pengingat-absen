<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramWebhookController;
use App\Services\HolidayService;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Jalankan listener Telegram Bot secara langsung (Long Polling untuk Localhost/Server)';

    public function handle(
        TelegramService $telegram,
        WhatsAppService $wa,
        HolidayService $holiday,
        TelegramWebhookController $controller
    ): int {
        if (!$telegram->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN belum diisi di file .env!');
            return 1;
        }

        $this->info('==================================================');
        $this->info('🤖 Telegram Bot Listener (Polling Mode) Aktif');
        $this->info('Siap menerima perintah dari HP Admin...');
        $this->info('==================================================');

        $offset = 0;

        while (true) {
            try {
                $updates = $telegram->getUpdates($offset);

                foreach ($updates as $update) {
                    $updateId = $update['update_id'];
                    $offset = $updateId + 1;

                    $msg = $update['message']['text'] ?? '';
                    $from = $update['message']['from']['first_name'] ?? 'User';

                    if (!empty($msg)) {
                        $this->line("[<info>" . date('H:i:s') . "</info>] Pesan dari {$from}: <comment>{$msg}</comment>");
                        $controller->processUpdate($update, $telegram, $wa, $holiday);
                    }
                }
            } catch (\Exception $e) {
                $this->error('Error polling: ' . $e->getMessage());
                sleep(2);
            }

            usleep(500000); // 0.5s
        }

        return 0;
    }
}
