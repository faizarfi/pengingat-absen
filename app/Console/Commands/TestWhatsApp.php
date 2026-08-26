<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;

class TestWhatsApp extends Command
{
    protected $signature = 'wa:test {phone} {message?}';
    protected $description = 'Test WhatsApp sending to a single number';

    public function handle(WhatsAppService $wa)
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message') ?? 'Test message from Laravel WhatsApp Service';

        $this->info("Testing WhatsApp to: {$phone}");
        $this->info("Message: {$message}");
        $this->line('');

        $result = $wa->sendMessage($phone, $message);

        $this->line('=== RESULT ===');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($result['success']) {
            $this->info('✅ Message sent successfully!');
        } elseif (isset($result['error']) && $result['error'] === 'rate_limited') {
            $this->warn('⏰ Rate limited - retry after ' . ($result['retry_after'] ?? 60) . ' seconds');
        } else {
            $this->error('❌ Failed (Status ' . ($result['status'] ?? 'unknown') . '): ' . ($result['error'] ?? 'Unknown error'));
        }
    }
}
