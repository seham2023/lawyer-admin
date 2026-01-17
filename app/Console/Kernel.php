<?php

namespace App\Console;

use App\Console\Commands\CheckPaymentStatuses;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check payment statuses every 5 minutes
        $schedule->command(CheckPaymentStatuses::class)->everyFiveMinutes();

        // Send scheduled reminders every minute
        $schedule->command('reminders:send')->everyMinute();

        // Cleanup old reminders daily at 2 AM
        $schedule->command('reminders:cleanup --force')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
