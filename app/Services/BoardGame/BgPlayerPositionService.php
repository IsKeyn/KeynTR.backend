<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Services\Entity\EntityService;

class BgPlayerPositionService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGamePlayerPosition::class,
            BoardGamePlayerPosition::CACHE_SERVICE,
            BoardGamePlayerPosition::DETAIL_RESOURCE,
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
