<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\Cache\AdminCacheService;
use App\Services\Cache\UserCacheService;
use App\Services\Observer\DefaultObserverService;

class RoleObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Role "created" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function created(Role $role)
    {
        $this->defaultObserverService->created(
            $role,
            'App\Services\Cache\RoleCacheService',
            'App\Services\RoleService'
        );

        $userCacheService = app(UserCacheService::class);
        $userCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Role "updated" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function updated(Role $role)
    {
        $this->defaultObserverService->updated(
            $role,
            'App\Services\Cache\RoleCacheService',
            'App\Services\RoleService'
        );

        $userCacheService = app(UserCacheService::class);
        $userCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Role "deleted" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function deleted(Role $role)
    {
        $this->defaultObserverService->deleted(
            $role,
            'App\Services\Cache\RoleCacheService',
            'App\Services\RoleService'
        );

        $userCacheService = app(UserCacheService::class);
        $userCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Role "restored" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function restored(Role $role)
    {
        $this->defaultObserverService->restored(
            $role,
            'App\Services\Cache\RoleCacheService',
            'App\Services\RoleService'
        );

        $userCacheService = app(UserCacheService::class);
        $userCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Role "force deleted" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function forceDeleted(Role $role)
    {
        $this->defaultObserverService->forceDeleted(
            $role,
            'App\Services\Cache\RoleCacheService'
        );

        $userCacheService = app(UserCacheService::class);
        $userCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
