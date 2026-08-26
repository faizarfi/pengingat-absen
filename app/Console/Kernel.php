<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // register command class if needed
        \App\Console\Commands\SendWaReminder::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // run every minute; command itself checks exact times
        $schedule->command('wa:send-reminders')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
