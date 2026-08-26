<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramTestCommand extends Command
{
    protected $signature = 'telegram:test {message? : Pesan yang ingin dikirim}';
    protected $description = 'Kirim pesan uji coba ke akun Telegram Admin';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Mengirim pesan uji coba ke akun Telegram Admin...');

        $text = $this->argument('message') ?: "🎉 <b>Bot Telegram Pengingat Absen Berhasil Terhubung!</b>\n\nSelamat datang Admin! Sistem Pengingat Absensi BPS kini siap dikendalikan langsung dari HP Anda.";

        $success = $telegram->sendAdmin($text);

        if ($success) {
            $this->info('✅ Pesan berhasil terkirim ke Telegram Admin!');
            return 0;
        } else {
            $this->error('❌ Gagal mengirim pesan ke Telegram. Periksa koneksi internet dan pastikan token serta chat ID di file .env sudah benar.');
            return 1;
        }
    }
}
