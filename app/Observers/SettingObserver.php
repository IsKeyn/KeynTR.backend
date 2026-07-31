<?php

namespace App\Observers;

use App\Models\BoardGame\BoardGame;
use App\Models\Setting;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Observer\DefaultObserverService;

class SettingObserver
{
    private const CACHE_SERVICE = Setting::CACHE_SERVICE;
    private const SERVICE = Setting::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Setting "created" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function created(Setting $setting)
    {
        $this->clearRelatedCache($setting);

        $this->defaultObserverService->created(
            $setting,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    /**
     * Handle the Setting "updated" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function updated(Setting $setting)
    {
        $this->clearRelatedCache($setting);

        $this->defaultObserverService->updated(
            $setting,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    /**
     * Handle the Setting "deleted" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function deleted(Setting $setting)
    {
        $this->clearRelatedCache($setting);

        $this->defaultObserverService->deleted(
            $setting,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    /**
     * Handle the Setting "restored" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function restored(Setting $setting)
    {
        $this->clearRelatedCache($setting);

        $this->defaultObserverService->restored(
            $setting,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    /**
     * Handle the Setting "force deleted" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function forceDeleted(Setting $setting)
    {
        $this->clearRelatedCache($setting);

        $this->defaultObserverService->forceDeleted(
            $setting,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($setting)
    {
        $setting->load(['entity']);

        if ($setting->entity_id !== null && $setting->entity_type === BoardGame::class) {
            $boardGameCacheService = app(BoardGameCacheService::class);
            $boardGameCacheService->clearDetailCacheAllTypes($setting->entity);
        }
    }
}
