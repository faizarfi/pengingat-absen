<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Commands & Schedules
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler Pengingat Absen (Dijalankan setiap menit untuk cek jadwal 30 menit sebelumnya, jam masuk, jam pulang)
Schedule::command('wa:send-reminders')->everyMinute()->withoutOverlapping();

// Sinkronisasi otomatis Hari Libur Nasional setiap hari pukul 00:05 WIB
Schedule::command('holidays:sync')->dailyAt('00:05');
