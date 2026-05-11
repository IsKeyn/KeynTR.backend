<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class UserObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the GamingPlatform "created" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function created(User $user)
    {
        $this->defaultObserverService->created(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "updated" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function updated(User $user)
    {
        $this->defaultObserverService->updated(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "deleted" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function deleted(User $user)
    {
        $this->defaultObserverService->deleted(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "restored" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function restored(User $user)
    {
        $this->defaultObserverService->restored(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "force deleted" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        $this->defaultObserverService->forceDeleted(
            $user,
            'App\Services\Cache\UserCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
