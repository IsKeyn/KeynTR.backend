<?php

namespace App\Observers;

use App\Models\Menu;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class MenuObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Menu "created" event.
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    public function created(Menu $menu)
    {
        $this->defaultObserverService->created(
            $menu,
            'App\Services\Cache\MenuCacheService',
            'App\Services\MenuService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Menu "updated" event.
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    public function updated(Menu $menu)
    {
        $this->defaultObserverService->updated(
            $menu,
            'App\Services\Cache\MenuCacheService',
            'App\Services\MenuService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Menu "deleted" event.
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    public function deleted(Menu $menu)
    {
        $this->defaultObserverService->deleted(
            $menu,
            'App\Services\Cache\MenuCacheService',
            'App\Services\MenuService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Menu "restored" event.
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    public function restored(Menu $menu)
    {
        $this->defaultObserverService->restored(
            $menu,
            'App\Services\Cache\MenuCacheService',
            'App\Services\MenuService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Menu "force deleted" event.
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    public function forceDeleted(Menu $menu)
    {
        $this->defaultObserverService->forceDeleted(
            $menu,
            'App\Services\Cache\MenuCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
