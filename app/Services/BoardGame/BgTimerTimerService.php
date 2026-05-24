<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Services\Entity\EntityService;

class BgTimerTimerService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGamePlayerTimer::class,
            'App\Services\Cache\BgPlayerTimerCacheService',
            'App\Http\Resources\Admin\BoardGame\BgPlayerTimer\DetailResource',
            $id,
            [],
            $forceRefresh,
            $withTrashed,
        );
    }
}
