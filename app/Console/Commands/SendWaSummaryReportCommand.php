<?php

namespace App\Console\Commands;

use App\Models\WaOutbox;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendWaSummaryReportCommand extends Command
{
    protected $signature = 'wa:report';
    protected $description = 'Kirim laporan ringkasan status pengiriman WhatsApp hari ini ke Telegram Admin';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Menyusun dan mengirim laporan pengiriman WhatsApp hari ini...');

        $sentToday = WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count();
        $failedToday = WaOutbox::failed()->today()->with('employee')->get();
        $failedCount = $failedToday->count();
        $pending = WaOutbox::pending()->count();
        $processing = WaOutbox::processing()->count();

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

        $report = "📊 <b>LAPORAN STATUS PENGIRIMAN WHATSAPP HARI INI</b>\n\n"
                . "📅 Tanggal : <b>" . now()->translatedFormat('l, d F Y') . "</b>\n\n"
                . "✅ <b>Berhasil Terkirim :</b> {$sentToday} Pesan\n"
                . "❌ <b>Gagal Dikirim     :</b> {$failedCount} Pesan\n"
                . "⏳ <b>Sisa Antrean      :</b> {$pending} Pending, {$processing} Diproses"
                . $failedDetails . "\n\n"
                . "💡 <i>Status sistem: WhatsApp Desktop PC Kantor beroperasi normal.</i>";

        $success = $telegram->sendAdmin($report);

        if ($success) {
            $this->info('✅ Laporan berhasil dikirim ke Telegram Admin!');
            return 0;
        } else {
            $this->error('❌ Gagal mengirim laporan ke Telegram Admin.');
            return 1;
        }
    }
}
