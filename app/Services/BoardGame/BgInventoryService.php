<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Services\Entity\EntityService;

class BgInventoryService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGameInventory::class,
            BoardGameInventory::CACHE_SERVICE,
            BoardGameInventory::DETAIL_RESOURCE,
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
