<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\PlayerGameShortResource;
use App\Models\BoardGame\PlayerGame;

class PlayerGameService
{
    public static function actionsWithGame($gameListGameId, $boardGameId)
    {
        $playerGame = PlayerGame::query()
            ->where('board_game_game_list_id', $gameListGameId)
            ->where('board_game_id', $boardGameId)
            ->where('status', '!=',PlayerGame::CURRENT)
            ->get();

        return PlayerGameShortResource::collection($playerGame);
    }
}
