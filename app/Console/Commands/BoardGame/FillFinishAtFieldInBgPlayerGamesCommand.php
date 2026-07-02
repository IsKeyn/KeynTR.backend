<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\PlayerGame;
use Illuminate\Console\Command;

class FillFinishAtFieldInBgPlayerGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-finish-at-field-in-bg-player-games-command';

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
        $playerGames = PlayerGame::all();

        foreach ($playerGames as $playerGame) {
            $playerGame->finished_at = $playerGame->updated_at;
            PlayerGame::withoutTimestamps(function () use ($playerGame) {
                $playerGame->save();
            });
        }

        $this->line('Комманда завершила работу');
    }
}
