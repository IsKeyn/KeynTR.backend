<?php

namespace App\Models\Traits;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;

trait ExtendModelForBoardGameTrait
{
    public function scopeFindByBoardGame($query, $boardGameId)
    {
        return $query->where('board_game_id', $boardGameId);
    }

    public function scopeFindByPlayer($query, $playerId)
    {
        return $query->where('bg_player_id', $playerId);
    }

    public function scopeFindByUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function boardGame()
    {
        return $this->belongsTo(BoardGame::class, 'board_game_id');
    }

    public function player()
    {
        return $this->belongsTo(BoardGamePlayer::class, 'bg_player_id');
    }
}
