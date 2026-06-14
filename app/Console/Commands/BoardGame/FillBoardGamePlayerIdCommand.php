<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerStatusEffect;
use Illuminate\Console\Command;

class FillBoardGamePlayerIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-board-game-player-id-command';

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
        $playerStatusEffects = PlayerStatusEffect::all();

        foreach ($playerStatusEffects as $playerStatusEffect) {
            if ($playerStatusEffect->user_id && $playerStatusEffect->board_game_id) {
                $player = BoardGamePlayer::query()
                    ->where('user_id', $playerStatusEffect->user_id)
                    ->where('board_game_id', $playerStatusEffect->board_game_id)
                    ->value('id');

                $playerStatusEffect->bg_player_id = $player;
                $playerStatusEffect->save();
            }
        }

        $playerStatusEffects = BoardGameInventory::all();

        foreach ($playerStatusEffects as $playerStatusEffect) {
            if ($playerStatusEffect->user_id && $playerStatusEffect->board_game_id) {
                $player = BoardGamePlayer::query()
                    ->where('user_id', $playerStatusEffect->user_id)
                    ->where('board_game_id', $playerStatusEffect->board_game_id)
                    ->value('id');

                $playerStatusEffect->bg_player_id = $player;
                $playerStatusEffect->save();
            }
        }

        $playerStatusEffects = PlayerGame::all();

        foreach ($playerStatusEffects as $playerStatusEffect) {
            if ($playerStatusEffect->user_id && $playerStatusEffect->board_game_id) {
                $player = BoardGamePlayer::query()
                    ->where('user_id', $playerStatusEffect->user_id)
                    ->where('board_game_id', $playerStatusEffect->board_game_id)
                    ->value('id');

                $playerStatusEffect->bg_player_id = $player;
                $playerStatusEffect->save();
            }
        }
    }
}
