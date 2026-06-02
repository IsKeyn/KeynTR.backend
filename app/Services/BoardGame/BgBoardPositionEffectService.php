<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardPositionEffect;
use App\Services\Entity\EntityService;

class BgBoardPositionEffectService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardPositionEffect::class,
            BoardPositionEffect::CACHE_SERVICE,
            BoardPositionEffect::DETAIL_RESOURCE,
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
