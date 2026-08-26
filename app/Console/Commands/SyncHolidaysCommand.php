<?php

namespace App\Console\Commands;

use App\Services\HolidayService;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    protected $signature = 'holidays:sync {year? : Tahun yang ingin disinkronkan}';
    protected $description = 'Sinkronisasi kalender hari libur nasional Indonesia ke database';

    public function handle(HolidayService $service): int
    {
        $year = $this->argument('year') ? (int) $this->argument('year') : (int) date('Y');
        $this->info("Menyinkronkan hari libur nasional Indonesia untuk tahun {$year}...");

        $count = $service->syncNationalHolidays($year);

        if ($count > 0) {
            $this->info("✅ Berhasil menyimpan {$count} hari libur nasional untuk tahun {$year}.");
        } else {
            $this->warn("⚠️ Tidak ada data baru atau gagal terhubung ke API hari libur.");
        }

        return 0;
    }
}
