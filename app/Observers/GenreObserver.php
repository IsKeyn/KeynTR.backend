<?php

namespace App\Observers;

use App\Models\Genre;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class GenreObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Genre "created" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function created(Genre $genre)
    {
        $this->defaultObserverService->created(
            $genre,
            'App\Services\Cache\GenreCacheService',
            'App\Services\GenreService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Genre "updated" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function updated(Genre $genre)
    {
        $this->defaultObserverService->updated(
            $genre,
            'App\Services\Cache\GenreCacheService',
            'App\Services\GenreService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Genre "deleted" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function deleted(Genre $genre)
    {
        $this->defaultObserverService->deleted(
            $genre,
            'App\Services\Cache\GenreCacheService',
            'App\Services\GenreService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Genre "restored" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function restored(Genre $genre)
    {
        $this->defaultObserverService->restored(
            $genre,
            'App\Services\Cache\GenreCacheService',
            'App\Services\GenreService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Genre "force deleted" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function forceDeleted(Genre $genre)
    {
        $this->defaultObserverService->forceDeleted(
            $genre,
            'App\Services\Cache\GenreCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
