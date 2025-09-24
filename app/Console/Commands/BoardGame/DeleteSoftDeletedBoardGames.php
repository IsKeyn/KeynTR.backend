<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use Illuminate\Console\Command;

class DeleteSoftDeletedBoardGames extends Command
{
    protected $signature = 'board-game:delete-soft-deleted-board-games';
    protected $description = 'Команда удаляет все настольные игры, удаленные с помощью "soft delete" и удаляет привязанные сущности, например игроков';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->line('НАЧАЛО работы комманды удаления настольных игр');

        $deletedBoardGame = BoardGame::onlyTrashed()->get();

        foreach ($deletedBoardGame as $boardGame) {
            // Уаделения игроков (board_game_players)
            $players = BoardGamePlayer::where('board_game_id', '=', $boardGame->id)->get();

            foreach ($players as $player) {
                $player->forceDelete();
            }

            // Удаление связанного медиа (media_bind)
            // Удаление комментариев
            // Удаление игр игрока (player_games)
            // Удаление статус эффектов (player_status_effects)

            // Удаления инвенторя игроков (board_game_inventories)
            // Удаление позиций игроков (board_game_player_positions)
            // Удаление таймеров (board_game_player_timers)
            // Удаление привязанных игр (board_game_game_lists)
            // Удаление привязанных прдметов (bg_items_bind)
            // Удаление логов (board_game_logs)
            //

//            $boardGame->forceDelete();
        }

        $this->line('КОНЕЦ работы комманды удаления настольных игр');
    }
}
