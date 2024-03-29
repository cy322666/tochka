<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
//         $schedule->command('messages:incoming')->everyFiveMinutes();

        $schedule->command('telescope:prune --hours=72')->daily();

        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->command('ord:get-persons')->everyFiveMinutes();
        $schedule->command('ord:get-contracts')->everyTenMinutes();
        $schedule->command('ord:get-pads')->everyTenMinutes();
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
