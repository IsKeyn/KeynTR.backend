<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BoardGameObserver
{
    private const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BoardGameCacheService';
    private const SERVICE = 'App\Services\BoardGame\BoardGameService';

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGame $boardGame)
    {
        $this->additionalActions($boardGame);

        $this->defaultObserverService->created(
            $boardGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGame $boardGame)
    {
        $this->additionalActions($boardGame);

        $this->defaultObserverService->updated(
            $boardGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGame $boardGame)
    {
        $this->additionalActions($boardGame);

        $this->defaultObserverService->deleted(
            $boardGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGame $boardGame)
    {
        $this->additionalActions($boardGame);

        $this->defaultObserverService->restored(
            $boardGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGame $boardGame)
    {
        $this->additionalActions($boardGame);

        $this->defaultObserverService->forceDeleted(
            $boardGame,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($boardGame)
    {
        $boardGame->load(['players', 'players.boardGame']);

        $this->clearRelatedCache($boardGame);
    }

    private function clearRelatedCache($boardGame)
    {
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListByBgCache($boardGame);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearBgListCache($boardGame);
    }
}
