<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\PlayerStatusEffect;
use App\Services\Entity\EntityService;

class PlayerStatusEffectService
{
    public $statusEffect = null;
    public $conditionData = [];

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            PlayerStatusEffect::class,
            PlayerStatusEffect::CACHE_SERVICE,
            PlayerStatusEffect::DETAIL_RESOURCE,
            $id,
            ['additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }
}
