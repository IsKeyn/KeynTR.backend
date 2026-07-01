<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use Illuminate\Console\Command;

class FillBoardGamePlayerIdCommand extends Command
{
    /* TODO После выполнения команды, проверить, что updated_at не изменились как минимум в таблице player_games */

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
                $playerStatusEffect->save(['timestamps' => false]);

                PlayerStatusEffect::withoutTimestamps(function () use ($playerStatusEffect) {
                    $playerStatusEffect->save();
                });
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
                BoardGameInventory::withoutTimestamps(function () use ($playerStatusEffect) {
                    $playerStatusEffect->save();
                });
            }
        }

        $playerStatusEffects = PlayerGame::all();

        foreach ($playerStatusEffects as $playerStatusEffect) {
            if ($playerStatusEffect->user_id && $playerStatusEffect->board_game_id) {
                $player = BoardGamePlayer::query()
                    ->where('user_id', $playerStatusEffect->user_id)
                    ->where('board_game_id', $playerStatusEffect->board_game_id)
                    ->value('id');

                $this->line('bg_player_id ID игрока: ' . $player);
                $playerStatusEffect->bg_player_id = $player;
                PlayerGame::withoutTimestamps(function () use ($playerStatusEffect) {
                    $playerStatusEffect->save();
                });
            }
        }

        $playerInteractions = PlayerInteractions::all();

        foreach ($playerInteractions as $playerInteraction) {
            if ($playerInteraction->created_by && $playerInteraction->board_game_id) {
                $player = BoardGamePlayer::query()
                    ->where('user_id', $playerInteraction->created_by)
                    ->where('board_game_id', $playerInteraction->board_game_id)
                    ->value('id');

                $this->line('PlayerInteractions ID игрока: ' . $player);
                $playerInteraction->bg_player_id = $player;
                PlayerInteractions::withoutTimestamps(function () use ($playerStatusEffect) {
                    $playerStatusEffect->save();
                });
            }
        }

        $boardGamePlayerPositions = BoardGamePlayerPosition::all();

        foreach ($boardGamePlayerPositions as $boardGamePlayerPosition) {
            if ($boardGamePlayerPosition->user_id && $boardGamePlayerPosition->board_game_id) {
                $player = BoardGamePlayer::query()
                    ->where('user_id', $boardGamePlayerPosition->user_id)
                    ->where('board_game_id', $boardGamePlayerPosition->board_game_id)
                    ->value('id');

                $this->line('BoardGamePlayerPosition ID игрока: ' . $player);
                $boardGamePlayerPosition->bg_player_id = $player;
                BoardGamePlayerPosition::withoutTimestamps(function () use ($playerStatusEffect) {
                    $playerStatusEffect->save();
                });
            }
        }
    }
}
