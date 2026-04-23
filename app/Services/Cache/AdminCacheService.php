<?php

namespace App\Services\Cache;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cache;

class AdminCacheService extends ServiceProvider
{
    public static function clearAdminAdditionalDataCache(): void
    {
        Cache::forget(SeriesCacheService::ADMIN_ADDDATA_PREFIX);
        Cache::forget(GameCacheService::ADMIN_ADDDATA_PREFIX);
    }
}
