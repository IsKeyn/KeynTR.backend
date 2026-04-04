<?php

namespace App\Observers;

use App\Models\Game;
use App\Services\Cache\GameCacheService;

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
    }

    /**
     * Handle the Game "deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function deleted(Game $game)
    {
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
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
        $gameCacheService->clearAdminDetailCacheById($game->id);
        $gameCacheService->clearDetailCacheBySlug($game->slug);
    }

    /**
     * Handle the Game "force deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function forceDeleted(Game $game)
    {
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
    }
}
