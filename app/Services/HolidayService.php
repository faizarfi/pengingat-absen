<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    /**
     * Cek apakah tanggal adalah hari libur (Weekend / Tanggal Merah Nasional / Cuti Bersama).
     */
    public function isHoliday(?Carbon $date = null, bool $includeWeekend = true): bool
    {
        $date = $date ?? Carbon::now();

        // 1. Cek Weekend (Sabtu & Minggu)
        if ($includeWeekend && ($date->isSaturday() || $date->isSunday())) {
            return true;
        }

        // 2. Cek Tanggal Merah di Database
        return Holiday::where('date', $date->toDateString())->exists();
    }

    /**
     * Dapatkan detail hari libur jika hari ini libur.
     */
    public function getHolidayInfo(?Carbon $date = null): ?Holiday
    {
        $date = $date ?? Carbon::now();
        return Holiday::where('date', $date->toDateString())->first();
    }

    /**
     * Sinkronisasi data hari libur nasional dari API publik Indonesia.
     */
    public function syncNationalHolidays(?int $year = null): int
    {
        $year = $year ?? (int) date('Y');
        $count = 0;

        try {
            // Coba endpoint 1: day-off-api
            $response = Http::timeout(10)->get("https://day-off-api.vercel.app/api?year={$year}");

            if ($response->successful()) {
                $data = $response->json();
                foreach ($data as $item) {
                    $holidayDate = $item['holiday_date'] ?? $item['date'] ?? null;
                    $holidayName = $item['holiday_name'] ?? $item['name'] ?? 'Hari Libur Nasional';
                    $isNational = $item['is_national_holiday'] ?? true;

                    if ($holidayDate) {
                        Holiday::updateOrCreate(
                            ['date' => Carbon::parse($holidayDate)->toDateString()],
                            [
                                'name'                => $holidayName,
                                'is_national_holiday' => (bool) $isNational,
                                'description'         => $item['description'] ?? null,
                            ]
                        );
                        $count++;
                    }
                }
            } else {
                // Fallback endpoint 2: api-harilibur
                $res2 = Http::timeout(10)->get("https://api-harilibur.vercel.app/api?year={$year}");
                if ($res2->successful()) {
                    $data2 = $res2->json();
                    foreach ($data2 as $item) {
                        if (isset($item['holiday_date'])) {
                            Holiday::updateOrCreate(
                                ['date' => Carbon::parse($item['holiday_date'])->toDateString()],
                                [
                                    'name'                => $item['holiday_name'] ?? 'Hari Libur',
                                    'is_national_holiday' => (bool) ($item['is_national_holiday'] ?? true),
                                ]
                            );
                            $count++;
                        }
                    }
                }
            }

            Log::info("HolidayService: Berhasil sinkronisasi {$count} hari libur untuk tahun {$year}");
        } catch (\Exception $e) {
            Log::warning("HolidayService: Gagal sinkronisasi API ({$e->getMessage()}). Menggunakan data fallback offline.");
        }

        // Jika API tidak merespons (misal offline atau tahun masa depan), isi dengan daftar standar
        if ($count === 0) {
            $count = $this->seedDefaultHolidays($year);
        }

        return $count;
    }

    /**
     * Data hari libur nasional Indonesia standar sebagai fallback.
     */
    protected function seedDefaultHolidays(int $year): int
    {
        $defaults = [
            "{$year}-01-01" => "Tahun Baru Masehi",
            "{$year}-01-27" => "Isra Mi'raj Nabi Muhammad SAW",
            "{$year}-02-17" => "Tahun Baru Imlek",
            "{$year}-03-21" => "Hari Suci Nyepi (Tahun Baru Saka)",
            "{$year}-03-20" => "Hari Raya Idul Fitri 1447 H (Hari 1)",
            "{$year}-03-21" => "Hari Raya Idul Fitri 1447 H (Hari 2)",
            "{$year}-04-03" => "Wafat Yesus Kristus",
            "{$year}-04-05" => "Hari Paskah",
            "{$year}-05-01" => "Hari Buruh Internasional",
            "{$year}-05-14" => "Kenaikan Yesus Kristus",
            "{$year}-05-27" => "Hari Raya Idul Adha 1447 H",
            "{$year}-05-31" => "Hari Raya Waisak",
            "{$year}-06-01" => "Hari Lahir Pancasila",
            "{$year}-06-16" => "Tahun Baru Islam 1448 H",
            "{$year}-08-17" => "Hari Proklamasi Kemerdekaan RI ke-81",
            "{$year}-08-25" => "Maulid Nabi Muhammad SAW",
            "{$year}-12-25" => "Hari Raya Natal",
        ];

        $inserted = 0;
        foreach ($defaults as $date => $name) {
            Holiday::updateOrCreate(
                ['date' => $date],
                ['name' => $name, 'is_national_holiday' => true]
            );
            $inserted++;
        }

        return $inserted;
    }
}
