<?php

namespace App\Observers;

use App\Models\Setting;
use App\Models\Version;
use App\Services\Cache\SettingCacheService;
use App\Services\SettingService;
use App\Services\VersionService;

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
        $versionCacheService->clearListCache(false, $setting->site_id, $setting->entity_type, $setting->entity_id);

        $version = SettingService::getById($setting->id, true)->toArray(request());
        VersionService::set($version, $setting->model, $setting->id, $setting->name, Version::TYPE_CREATE);
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
        $versionCacheService->clearListCache(false, $setting->site_id, $setting->entity_type, $setting->entity_id);

        $version = SettingService::getById($setting->id, true)->toArray(request());
        VersionService::set($version, $setting->model, $setting->id, $setting->name, Version::TYPE_CREATE);
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
        $versionCacheService->clearListCache(false, $setting->site_id, $setting->entity_type, $setting->entity_id);

        if (!$setting->isForceDeleting()) {
            $version = SettingService::getById($setting->id, true, true)->toArray(request());
            VersionService::set($version, $setting->model, $setting->id, $setting->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $setting->model)
                ->where('entity_id', $setting->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $setting->model, $setting->id, $setting->name, Version::TYPE_DELETE);
            }
            return;
        }
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
        $versionCacheService->clearListCache(false, $setting->site_id, $setting->entity_type, $setting->entity_id);

        $version = SettingService::getById($setting->id, true, true)->toArray(request());
        VersionService::set($version, $setting->model, $setting->id, $setting->name, Version::TYPE_RECOVERY);
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
        $versionCacheService->clearListCache(false, $setting->site_id, $setting->entity_type, $setting->entity_id);
    }
}
