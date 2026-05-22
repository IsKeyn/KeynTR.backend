<?php

namespace App\Observers;

use App\Models\Tag;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class TagObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Tag "created" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function created(Tag $tag)
    {
        $this->defaultObserverService->created(
            $tag,
            'App\Services\Cache\TagCacheService',
            'App\Services\TagService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Tag "updated" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function updated(Tag $tag)
    {
        $this->defaultObserverService->updated(
            $tag,
            'App\Services\Cache\TagCacheService',
            'App\Services\TagService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Tag "deleted" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function deleted(Tag $tag)
    {
        $this->defaultObserverService->deleted(
            $tag,
            'App\Services\Cache\TagCacheService',
            'App\Services\TagService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Tag "restored" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function restored(Tag $tag)
    {
        $this->defaultObserverService->restored(
            $tag,
            'App\Services\Cache\TagCacheService',
            'App\Services\TagService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Tag "force deleted" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function forceDeleted(Tag $tag)
    {
        $this->defaultObserverService->forceDeleted(
            $tag,
            'App\Services\Cache\TagCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
