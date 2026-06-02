<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameGameList;
use App\Services\Entity\EntityService;

class BgGameListService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGameGameList::class,
            BoardGameGameList::CACHE_SERVICE,
            BoardGameGameList::DETAIL_RESOURCE,
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
