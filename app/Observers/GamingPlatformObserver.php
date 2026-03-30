<?php

namespace App\Observers;

use App\Models\GamingPlatform;
use App\Services\Cache\GameCacheService;

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
        $this->clearCaches();
    }

    /**
     * Handle the GamingPlatform "updated" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function updated(GamingPlatform $gamingPlatform)
    {
        $this->clearCaches();
    }

    /**
     * Handle the GamingPlatform "deleted" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function deleted(GamingPlatform $gamingPlatform)
    {
        $this->clearCaches();
    }

    /**
     * Handle the GamingPlatform "restored" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function restored(GamingPlatform $gamingPlatform)
    {
        $this->clearCaches();
    }

    /**
     * Handle the GamingPlatform "force deleted" event.
     *
     * @param  \App\Models\GamingPlatform  $gamingPlatform
     * @return void
     */
    public function forceDeleted(GamingPlatform $gamingPlatform)
    {
        $this->clearCaches();
    }

    private function clearCaches()
    {
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearAdminAddDataCache();
    }
}
