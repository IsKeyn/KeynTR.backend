<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected $commands = [
        Commands\YouTubeFetchLastVideos::class,
        Commands\SetLogs::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('YouTube:FetchLastVideos')->daily(); // Запрос новый видео с канала каждый день
        $schedule->command('auth:clear-resets')->everyFifteenMinutes(); // Сброс истекших токенов сброса пароля
        // $schedule->command('log:set')->everyMinute();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
