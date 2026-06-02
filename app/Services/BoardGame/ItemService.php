<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\Item;
use App\Services\Entity\EntityService;

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

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Item::class,
            Item::CACHE_SERVICE,
            Item::DETAIL_RESOURCE,
            $id,
            [
                'tags',
                'additionalFields',
                'media',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }
}
