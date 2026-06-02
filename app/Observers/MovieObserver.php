<?php

namespace App\Observers;

use App\Models\Movie;
use App\Services\Observer\DefaultObserverService;

class MovieObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Movie "created" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function created(Movie $movie)
    {
        $this->defaultObserverService->created(
            $movie,
            'App\Services\Cache\MovieCacheService',
            'App\Services\MovieService'
        );
    }

    /**
     * Handle the Movie "updated" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function updated(Movie $movie)
    {
        $this->defaultObserverService->updated(
            $movie,
            'App\Services\Cache\MovieCacheService',
            'App\Services\MovieService'
        );
    }

    /**
     * Handle the Movie "deleted" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function deleted(Movie $movie)
    {
        $this->defaultObserverService->deleted(
            $movie,
            'App\Services\Cache\MovieCacheService',
            'App\Services\MovieService'
        );
    }

    /**
     * Handle the Movie "restored" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function restored(Movie $movie)
    {
        $this->defaultObserverService->restored(
            $movie,
            'App\Services\Cache\MovieCacheService',
            'App\Services\MovieService'
        );
    }

    /**
     * Handle the Movie "force deleted" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function forceDeleted(Movie $movie)
    {
        $this->defaultObserverService->forceDeleted(
            $movie,
            'App\Services\Cache\MovieCacheService'
        );
    }
}
