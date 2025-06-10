<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\Timer;
use Illuminate\Console\Command;

class SetTimerForAllUser extends Command
{
    protected $signature = 'board-game:setUserTimer';
    protected $description = 'Команда добавляет';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды обработки времени игры');

        $boardGamePlayers = BoardGamePlayer::query()->where('board_game_id', 1)->get();
        $boardGame = BoardGame::query()->where('id', 1)->first();

        foreach ($boardGamePlayers as $player) {
            $timer = Timer::query()
                ->where('user_id', $player->user_id)
                ->where('slug', 'main')
                ->where('board_game_id', 1)
                ->first();

            if (!$timer) {
                $timerFields = [
                    'user_id' => $player->user_id,
                    'board_game_id' => $boardGame->id,
                    'name' => $boardGame->name,
                    'limit' => 100*60*60,
                    'slug' => 'main',
                    'created_by' => $player->user_id,
                ];

                if (Timer::create($timerFields)) {
                    $this->line('Создан таймер для пользователя с ID: ' . $player->user_id);
                }
            }
        }

        $this->line('КОНЕЦ работы комманды обработки времени игры');
    }
}
