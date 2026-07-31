<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\Timer;
use App\Services\BoardGame\TimerService;
use Illuminate\Console\Command;

class FillElapsedSecondsInTimerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-elapsed-seconds-in-timer-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды');

        $timers = Timer::with('playerTimer')
            ->select(['id', 'name', 'limit', 'user_id', 'board_game_id', 'slug', 'settings', 'elapsed_seconds'])
            ->get();

        foreach ($timers as $timer) {
            $statusData = TimerService::getTimerStatus($timer);

            if (
                $statusData
                && isset($statusData['time'])
                && $timer->elapsed_seconds !== $statusData['time']
            ) {
                $timer->elapsed_seconds = $statusData['time'];
                $timer->saveQuietly();
            }
        }

        $this->line('КОНЕЦ работы комманды');
    }
}
