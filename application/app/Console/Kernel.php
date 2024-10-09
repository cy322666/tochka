<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('telescope:prune --hours=24')->daily();

        $schedule->command('app:cron2-check')->dailyAt('17:00:00');

        $schedule->command('ord:get-persons')->everyMinute();
        $schedule->command('ord:get-creative')->everyTenMinutes();
        $schedule->command('ord:get-contracts')->everyFiveMinutes();
        $schedule->command('ord:get-pads')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
