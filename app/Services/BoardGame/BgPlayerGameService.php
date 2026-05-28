<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\PlayerGame;
use App\Services\Entity\EntityService;

class BgPlayerGameService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            PlayerGame::class,
            PlayerGame::CACHE_SERVICE,
            PlayerGame::DETAIL_RESOURCE,
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
