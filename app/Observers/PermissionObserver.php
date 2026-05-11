<?php

namespace App\Observers;

use App\Models\Permission;
use App\Services\Cache\AdminCacheService;
use App\Services\Cache\RoleCacheService;
use App\Services\Cache\UserCacheService;
use App\Services\Observer\DefaultObserverService;

class PermissionObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Permission "created" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function created(Permission $permission)
    {
        $this->defaultObserverService->created(
            $permission,
            'App\Services\Cache\PermissionCacheService',
            'App\Services\PermissionService'
        );

        $userCacheService = app(UserCacheService::class);
        $roleCacheService = app(RoleCacheService::class);

        $userCacheService->clearAllDetailCache();
        $roleCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Permission "updated" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function updated(Permission $permission)
    {
        $this->defaultObserverService->updated(
            $permission,
            'App\Services\Cache\PermissionCacheService',
            'App\Services\PermissionService'
        );

        $userCacheService = app(UserCacheService::class);
        $roleCacheService = app(RoleCacheService::class);

        $userCacheService->clearAllDetailCache();
        $roleCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Permission "deleted" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function deleted(Permission $permission)
    {
        $this->defaultObserverService->deleted(
            $permission,
            'App\Services\Cache\PermissionCacheService',
            'App\Services\PermissionService'
        );

        $userCacheService = app(UserCacheService::class);
        $roleCacheService = app(RoleCacheService::class);

        $userCacheService->clearAllDetailCache();
        $roleCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Permission "restored" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function restored(Permission $permission)
    {
        $this->defaultObserverService->restored(
            $permission,
            'App\Services\Cache\PermissionCacheService',
            'App\Services\PermissionService'
        );

        $userCacheService = app(UserCacheService::class);
        $roleCacheService = app(RoleCacheService::class);

        $userCacheService->clearAllDetailCache();
        $roleCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Permission "force deleted" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function forceDeleted(Permission $permission)
    {
        $this->defaultObserverService->forceDeleted(
            $permission,
            'App\Services\Cache\PermissionCacheService'
        );

        $userCacheService = app(UserCacheService::class);
        $roleCacheService = app(RoleCacheService::class);

        $userCacheService->clearAllDetailCache();
        $roleCacheService->clearAllDetailCache();

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
