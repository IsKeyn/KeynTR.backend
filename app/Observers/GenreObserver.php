<?php

namespace App\Observers;

use App\Models\Genre;
use App\Services\Cache\GameCacheService;

class GenreObserver
{
    /**
     * Handle the Genre "created" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function created(Genre $genre)
    {
        $this->clearCaches();
    }

    /**
     * Handle the Genre "updated" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function updated(Genre $genre)
    {
        $this->clearCaches();
    }

    /**
     * Handle the Genre "deleted" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function deleted(Genre $genre)
    {
        $this->clearCaches();
    }

    /**
     * Handle the Genre "restored" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function restored(Genre $genre)
    {
        $this->clearCaches();
    }

    /**
     * Handle the Genre "force deleted" event.
     *
     * @param  \App\Models\Genre  $genre
     * @return void
     */
    public function forceDeleted(Genre $genre)
    {
        $this->clearCaches();
    }

    private function clearCaches()
    {
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearAdminAddDataCache();
    }
}
