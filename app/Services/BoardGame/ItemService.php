<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameInventory;

class ItemService
{
    public static function addToInventory($userId, $boardGameId, $boardGameItemId)
    {
        $fields = [
            'user_id' => $userId,
            'created_by' => $userId,
            'board_game_id' => $boardGameId,
            'board_game_item_id' => $boardGameItemId,
        ];

        return BoardGameInventory::create($fields);
    }
}
