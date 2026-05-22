<?php

namespace App\Services;

use App\Services\Entity\EntityService;

class SettingService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Setting',
            'App\Services\Cache\SettingCacheService',
            'App\Http\Resources\Admin\Setting\DetailResource',
            $id,
            ['tags', 'additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }
}
