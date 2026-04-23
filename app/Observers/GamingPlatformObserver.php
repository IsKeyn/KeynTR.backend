<?php

namespace App\Observers;

use App\Models\GamingPlatform;
use App\Services\Cache\AdminCacheService;

class GamingPlatformObserver
{
    /**
     * Handle the GamingPlatform "created" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function created(GamingPlatform $gamingPlatform)
    {
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
        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
