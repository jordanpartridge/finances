<?php

declare(strict_types=1);

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
        // Fetch stock prices every 15 minutes during US market trading hours
        // Trading hours: 9:30 AM - 4:00 PM ET, Monday-Friday
        $schedule->command('prices:fetch')
            ->everyFifteenMinutes()
            ->timezone('America/New_York')
            ->between('9:30', '16:00')
            ->weekdays()
            ->name('fetch-stock-prices')
            ->onSuccess(function () {
                \Log::info('Stock prices fetched successfully');
            })
            ->onFailure(function () {
                \Log::error('Failed to fetch stock prices');
            });
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
