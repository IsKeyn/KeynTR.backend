<?php

namespace App\Observers;

use App\Models\BoardGame\BoardGameGameList;
use App\Models\Game;
use App\Models\Version;
use App\Services\Cache\AdminCacheService;
use App\Services\Cache\GameCacheService;
use App\Services\Cache\SeriesCacheService;
use App\Services\GameService;
use App\Services\VersionService;

class GameObserver
{
    /**
     * Handle the Game "created" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function created(Game $game)
    {
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
        $gameCacheService->clearAdminDetailCacheById($game->id);
        $gameCacheService->clearDetailCacheBySlug($game->slug);

        AdminCacheService::clearAdminAdditionalDataCache();

        $version = GameService::getGameById($game->id, true)->toArray(request());
        VersionService::set($version, $game->model, $game->id, $game->name, Version::TYPE_CREATE);
    }

    /**
     * Handle the Game "updated" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function updated(Game $game)
    {
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
        $gameCacheService->clearAdminDetailCacheById($game->id);
        $gameCacheService->clearDetailCacheBySlug($game->slug);

        AdminCacheService::clearAdminAdditionalDataCache();

        $version = GameService::getGameById($game->id, true, true)->toArray(request());
        VersionService::set($version, $game->model, $game->id, $game->name, Version::TYPE_UPDATE);

        /* Удаляем кеш связанных сущностей */
        if ($game->series) {
            $seriesCacheService = app(SeriesCacheService::class);

            foreach ($game->series as $series) {
                $seriesCacheService->clearDetailCacheBySlug($series->slug);
                $seriesCacheService->clearAdminDetailCacheById($series->id);
            }
        }
    }

    /**
     * Handle the Game "deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function deleted(Game $game)
    {
        AdminCacheService::clearAdminAdditionalDataCache();

        if (!$game->isForceDeleting()) {
            $version = GameService::getGameById($game->id, true, true)->toArray(request());
            VersionService::set($version, $game->model, $game->id, $game->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $game->model)
                ->where('entity_id', $game->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $game->model, $game->id, $game->name, Version::TYPE_DELETE);
            }
            return;
        }

        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
        $gameCacheService->clearAdminDetailCacheById($game->id);
        $gameCacheService->clearDetailCacheBySlug($game->slug);
    }

    /**
     * Handle the Game "restored" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function restored(Game $game)
    {
        AdminCacheService::clearAdminAdditionalDataCache();

        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
        $gameCacheService->clearAdminDetailCacheById($game->id);
        $gameCacheService->clearDetailCacheBySlug($game->slug);

        $version = GameService::getGameById($game->id, true, true)->toArray(request());
        VersionService::set($version, $game->model, $game->id, $game->name, Version::TYPE_RECOVERY);
    }

    /**
     * Handle the Game "force deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function forceDeleted(Game $game)
    {
        AdminCacheService::clearAdminAdditionalDataCache();

        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();

        // Удаление связей
        $game->titleImage()->detach();
        $game->tags()->detach();

        $game->additionalFields()->delete();
        $game->comments()->delete();
        $game->views()->delete();
        $game->likes()->delete();
        $game->seo()->delete();
        $game->menu()->delete();
        $game->blocks()->delete();

        $game->cover()->detach();
        $game->dates()->detach();
        $game->anonsDates()->detach();
        $game->gamePlatform()->detach();
        $game->series()->detach();
        $game->groups()->detach();
        $game->genres()->detach();
        $game->company()->detach();
        $game->link()->detach();

        BoardGameGameList::where('game_id', $game->id)->delete();
    }
}
