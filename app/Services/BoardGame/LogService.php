<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameLog;
use App\Services\Entity\EntityService;

class LogService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGameLog::class,
            BoardGameLog::CACHE_SERVICE,
            BoardGameLog::DETAIL_RESOURCE,
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
