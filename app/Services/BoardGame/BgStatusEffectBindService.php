<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\StatusEffectBind;
use App\Services\Entity\EntityService;

class BgStatusEffectBindService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            StatusEffectBind::class,
            StatusEffectBind::CACHE_SERVICE,
            StatusEffectBind::DETAIL_RESOURCE,
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
