<?php

namespace App\Observers;

use App\Models\Setting;
use App\Services\Cache\SettingCacheService;

class SettingObserver
{
    /**
     * Handle the Setting "created" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function created(Setting $setting)
    {
        $versionCacheService = app(SettingCacheService::class);
        $versionCacheService->clearListCache();
        $versionCacheService->clearListCacheByEntity($setting->entity_type, $setting->entity_id);
    }

    /**
     * Handle the Setting "updated" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function updated(Setting $setting)
    {
        $versionCacheService = app(SettingCacheService::class);
        $versionCacheService->clearListCache();
    }

    /**
     * Handle the Setting "deleted" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function deleted(Setting $setting)
    {
        $versionCacheService = app(SettingCacheService::class);
        $versionCacheService->clearListCache();
    }

    /**
     * Handle the Setting "restored" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function restored(Setting $setting)
    {
        $versionCacheService = app(SettingCacheService::class);
        $versionCacheService->clearListCache();
    }

    /**
     * Handle the Setting "force deleted" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function forceDeleted(Setting $setting)
    {
        $versionCacheService = app(SettingCacheService::class);
        $versionCacheService->clearListCache();
    }
}
