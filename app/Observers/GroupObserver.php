<?php

namespace App\Observers;

use App\Models\Group;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class GroupObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Group "created" event.
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    public function created(Group $group)
    {
        $this->defaultObserverService->created(
            $group,
            'App\Services\Cache\GroupCacheService',
            'App\Services\GroupService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Group "updated" event.
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    public function updated(Group $group)
    {
        $this->defaultObserverService->updated(
            $group,
            'App\Services\Cache\GroupCacheService',
            'App\Services\GroupService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Group "deleted" event.
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    public function deleted(Group $group)
    {
        $this->defaultObserverService->deleted(
            $group,
            'App\Services\Cache\GroupCacheService',
            'App\Services\GroupService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Group "restored" event.
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    public function restored(Group $group)
    {
        $this->defaultObserverService->restored(
            $group,
            'App\Services\Cache\GroupCacheService',
            'App\Services\GroupService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Group "force deleted" event.
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    public function forceDeleted(Group $group)
    {
        $this->defaultObserverService->forceDeleted(
            $group,
            'App\Services\Cache\GroupCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
