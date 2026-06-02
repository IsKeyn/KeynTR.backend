<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Services\Entity\EntityService;

class BgBoardPositionEffectBindService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardPositionEffectsBind::class,
            BoardPositionEffectsBind::CACHE_SERVICE,
            BoardPositionEffectsBind::DETAIL_RESOURCE,
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
