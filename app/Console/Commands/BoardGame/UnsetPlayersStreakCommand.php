<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\PlayerGame;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UnsetPlayersStreakCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board-game:unset-players-streak';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда удаляет стрик, у игроков, которые в течении последней недели не прошли ни одной игры';

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
        $this->line('НАЧАЛО работы комманды сброса стриков');

        $boardGamesList = BoardGame::active()
            ->where('is_close', false)
            ->where('started_at', '<', Carbon::now())
            ->where('ended_at', '>', Carbon::now())->get();

        foreach ($boardGamesList as $boardGame) {
            $this->line('Обработка настолькой игры ' . $boardGame->name);
            foreach ($boardGame->players as $player) {
                if ($player->active && $player->streak > 0) {

                    $games = PlayerGame::findByBoardGame($boardGame->id)
                        ->findByUserId($player->user_id)
                        ->where('status', PlayerGame::COMPLETED)
                        ->where('updated_at', '>=', Carbon::now()->subWeek())
                        ->get();

                    if ($games->count() === 0) {
                        $player->streak = 0;
                        $player->save();

                        $this->line('Игрок ' . $player->user->name . ' стрик сброшен в ' . $boardGame->name);
                        Log::channel('streak')->info(
                            'Сброс стрика',
                            [
                                'user_id' => $player->user_id,
                                'user_name' => $player->user->name,
                                'board_game' => $boardGame->name,
                                'streak' => $player->streak,
                            ]
                        );
                    } else {
                        $this->line('Игрок ' . $player->user->name . ' стрик НЕ сброшен в ' . $boardGame->name);
                        Log::channel('streak')->info(
                            'Стрик сохранен',
                            [
                                'user_id' => $player->user_id,
                                'user_name' => $player->user->name,
                                'board_game' => $boardGame->name,
                                'streak' => $player->streak,
                            ]
                        );
                    }
                }
            }
        }

        $this->line('КОНЕЦ работы комманды сброса стрика');
        return 0;
    }
}
