<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameLog;

class LogService
{
    public static function addLog($userId, $boardGameId, $message)
    {
        $newEntry = [
            'board_game_id' => $boardGameId,
            'message' => $message,
            'created_by' => $userId,
        ];

        return BoardGameLog::create($newEntry);
    }
}
