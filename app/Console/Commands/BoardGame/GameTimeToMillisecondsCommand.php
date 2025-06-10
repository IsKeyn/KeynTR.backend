<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\PlayerGame;
use Illuminate\Console\Command;

class GameTimeToMillisecondsCommand extends Command
{
    protected $signature = 'board-game:gameTimeConvert';
    protected $description = 'Команда переводит время игры из формата 1:30 в 324000';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды обработки времени игры');

        $playersGame = PlayerGame::query()->get();

        foreach ($playersGame as $game) {
            if ($game->time) {
                $parts = explode(":", $game->time);

                if (count($parts) > 1) {
                    echo '<pre>';
                    print_r($game->time);
                    echo '</pre>';

                    $time = 0;

                    if (isset($parts[0])) {
                        $time += $parts[0] * 60 * 60;
                    }

                    if (isset($parts[1])) {
                        $time += $parts[1] * 60;
                    }

                    if (isset($parts[2])) {
                        $time += $parts[2];
                    }

                    $fields = array(
                        'time' => $time,
                    );

                    if ($game->update($fields)) {
                        $this->line('Время обновлено у элемента с ID: ' . $game->id . ' | ' . $game->time . ' => ' . $time);
                    }
                }
            }
        }

        $this->line('КОНЕЦ работы комманды обработки времени игры');
    }
}
