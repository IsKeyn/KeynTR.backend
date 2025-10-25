<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\Timer;
use App\Services\BoardGame\TimerService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StopLimitedTimerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board-game:stop-limited-timer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Комманда останавливает таймеры, с лимитом, которые запущены и превысили лимит';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды остановки таймеров');

        $boardGamesList = BoardGame::active()
            ->where('is_close', false)
            ->where('started_at', '<', Carbon::now())
            ->where('ended_at', '>', Carbon::now())->get();

        foreach ($boardGamesList as $boardGame) {
            $this->line('Обработка настолькой игры ' . $boardGame->name);

            foreach ($boardGame->players as $player) {
                $timers = Timer::active()
                    ->findByBoardGame($boardGame->id)
                    ->findByUserId($player->user->id)
                    ->where('limit', '!=', null)->get();

                foreach ($timers as $timer) {
                    $this->line('Обработка таймера игрока ' . $player->user->name . ', под названием ' . $timer->name);
                    $lastField = $timer->playerTimer->last();

                    if ($lastField && $lastField->time_stop) {
                        $status = TimerService::getTimerStatus($timer);

                        if ($status['time'] >= $status['limit']) {
                            $this->line('Таймер игрока ' . $player->user->name . ' под названием ' . $status['name'] . ' был остановлен, так как достиг лимита');
                        }
                    }
                }
            }
        }

        $this->line('КОНЕЦ работы комманды остановки таймеров');
        return 0;
    }
}
