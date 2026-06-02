<?php

namespace App\Observers;

use App\Models\GamingPlatform;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class GamingPlatformObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the GamingPlatform "created" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function created(GamingPlatform $gamingPlatform)
    {
        $this->defaultObserverService->created(
            $gamingPlatform,
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Services\GamingPlatformService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "updated" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function updated(GamingPlatform $gamingPlatform)
    {
        $this->defaultObserverService->updated(
            $gamingPlatform,
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Services\GamingPlatformService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "deleted" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function deleted(GamingPlatform $gamingPlatform)
    {
        $this->defaultObserverService->deleted(
            $gamingPlatform,
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Services\GamingPlatformService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "restored" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function restored(GamingPlatform $gamingPlatform)
    {
        $this->defaultObserverService->restored(
            $gamingPlatform,
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Services\GamingPlatformService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "force deleted" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function forceDeleted(GamingPlatform $gamingPlatform)
    {
        $this->defaultObserverService->forceDeleted(
            $gamingPlatform,
            'App\Services\Cache\GamingPlatformCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
