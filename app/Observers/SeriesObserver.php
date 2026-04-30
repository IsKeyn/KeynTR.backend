<?php

namespace App\Observers;

use App\Models\Series;
use App\Models\Version;
use App\Services\Cache\GameCacheService;
use App\Services\Cache\SeriesCacheService;
use App\Services\SeriesService;
use App\Services\VersionService;

class SeriesObserver
{
    /**
     * Handle the Series "created" event.
     *
     * @param  \App\Models\Series  $series
     * @return void
     */
    public function created(Series $series)
    {
        $seriesCacheService = app(SeriesCacheService::class);
        $seriesCacheService->clearListCache();
        $seriesCacheService->clearAdminDetailCacheById($series->id);
        $seriesCacheService->clearDetailCacheBySlug($series->slug);

        $version = SeriesService::getById($series->id, true)->toArray(request());
        VersionService::set($version, $series->model, $series->id, $series->name, Version::TYPE_CREATE);
    }

    /**
     * Handle the Series "updated" event.
     *
     * @param  \App\Models\Series  $series
     * @return void
     */
    public function updated(Series $series)
    {
        $seriesCacheService = app(SeriesCacheService::class);
        $seriesCacheService->clearListCache();
        $seriesCacheService->clearAdminDetailCacheById($series->id);
        $seriesCacheService->clearDetailCacheBySlug($series->slug);

        $version = SeriesService::getById($series->id, true, true)->toArray(request());
        VersionService::set($version, $series->model, $series->id, $series->name, Version::TYPE_UPDATE);

        /* Удаляем кеш связанных сущностей */
        if ($series->game) {
            $gameCacheService = app(GameCacheService::class);

            foreach ($series->game as $game) {
                $gameCacheService->clearDetailCacheBySlug($game->slug);
                $gameCacheService->clearAdminDetailCacheById($game->id);
            }
        }
    }

    /**
     * Handle the Series "deleted" event.
     *
     * @param  \App\Models\Series  $series
     * @return void
     */
    public function deleted(Series $series)
    {
        if (!$series->isForceDeleting()) {
            $version = SeriesService::getById($series->id, true, true)->toArray(request());
            VersionService::set($version, $series->model, $series->id, $series->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $series->model)
                ->where('entity_id', $series->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $series->model, $series->id, $series->name, Version::TYPE_DELETE);
            }
            return;
        }

        $seriesCacheService = app(SeriesCacheService::class);
        $seriesCacheService->clearListCache();
        $seriesCacheService->clearAdminDetailCacheById($series->id);
        $seriesCacheService->clearDetailCacheBySlug($series->slug);
    }

    /**
     * Handle the Series "restored" event.
     *
     * @param  \App\Models\Series  $series
     * @return void
     */
    public function restored(Series $series)
    {
        $seriesCacheService = app(SeriesCacheService::class);
        $seriesCacheService->clearListCache();
        $seriesCacheService->clearAdminDetailCacheById($series->id);
        $seriesCacheService->clearDetailCacheBySlug($series->slug);

        $version = SeriesService::getById($series->id, true, true)->toArray(request());
        VersionService::set($version, $series->model, $series->id, $series->name, Version::TYPE_RECOVERY);
    }

    /**
     * Handle the Series "force deleted" event.
     *
     * @param  \App\Models\Series  $series
     * @return void
     */
    public function forceDeleted(Series $series)
    {
        $seriesCacheService = app(SeriesCacheService::class);
        $seriesCacheService->clearListCache();

        // Удаление связей
        $series->tags()->detach();

        $series->additionalFields()->delete();
        $series->comments()->delete();
        $series->views()->delete();
        $series->likes()->delete();
        $series->seo()->delete();

        $series->genres()->detach();
        $series->company()->detach();
        $series->link()->detach();
    }
}
