<?php

namespace App\Models\Traits;

use App\Models\BoardGame\BoardGame;

trait ExtendModelForBoardGameTrait
{
    public function scopeFindByBoardGame($query, $boardGameId)
    {
        return $query->where('board_game_id', $boardGameId);
    }

    public function scopeFindByUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function boardGame()
    {
        return $this->belongsTo(BoardGame::class, 'board_game_id');
    }
}
