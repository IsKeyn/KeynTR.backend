<?php

namespace App\Observers;

use App\Models\Version;
use App\Services\Cache\VersionCacheService;

class VersionObserver
{
    /**
     * Handle the Version "created" event.
     *
     * @param  \App\Models\Version  $version
     * @return void
     */
    public function created(Version $version)
    {
        $versionCacheService = app(VersionCacheService::class);
        $versionCacheService->clearListCache();
        $versionCacheService->clearListCacheByEntity($version->entity->model, $version->entity->id);
    }

    /**
     * Handle the Version "updated" event.
     *
     * @param  \App\Models\Version  $version
     * @return void
     */
    public function updated(Version $version)
    {
        $versionCacheService = app(VersionCacheService::class);
        $versionCacheService->clearListCache();
    }

    /**
     * Handle the Version "deleted" event.
     *
     * @param  \App\Models\Version  $version
     * @return void
     */
    public function deleted(Version $version)
    {
        $versionCacheService = app(VersionCacheService::class);
        $versionCacheService->clearListCache();
    }

    /**
     * Handle the Version "restored" event.
     *
     * @param  \App\Models\Version  $version
     * @return void
     */
    public function restored(Version $version)
    {
        $versionCacheService = app(VersionCacheService::class);
        $versionCacheService->clearListCache();
    }

    /**
     * Handle the Version "force deleted" event.
     *
     * @param  \App\Models\Version  $version
     * @return void
     */
    public function forceDeleted(Version $version)
    {
        $versionCacheService = app(VersionCacheService::class);
        $versionCacheService->clearListCache();
    }
}
