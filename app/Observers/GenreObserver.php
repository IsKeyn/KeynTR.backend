<?php

namespace App\Observers;

use App\Models\Genre;
use App\Services\Cache\AdminCacheService;

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
        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
