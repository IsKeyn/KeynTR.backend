<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\ItemBind;
use App\Services\Entity\EntityService;

class BgItemBindService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            ItemBind::class,
            ItemBind::CACHE_SERVICE,
            ItemBind::DETAIL_RESOURCE,
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
