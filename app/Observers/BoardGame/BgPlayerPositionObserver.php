<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BgPlayerPositionObserver
{
    private const CACHE_SERVICE = BoardGamePlayerPosition::CACHE_SERVICE;
    private const SERVICE = BoardGamePlayerPosition::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->clearRelatedCache($boardGamePlayerPosition);

        $this->defaultObserverService->created(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->clearRelatedCache($boardGamePlayerPosition);

        $this->defaultObserverService->updated(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->clearRelatedCache($boardGamePlayerPosition);

        $this->defaultObserverService->deleted(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->clearRelatedCache($boardGamePlayerPosition);

        $this->defaultObserverService->restored(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->clearRelatedCache($boardGamePlayerPosition);

        $this->defaultObserverService->forceDeleted(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($boardGamePlayerPosition)
    {
        $boardGamePlayerPosition->load(['boardGame', 'boardGame.players']);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearBgListCache($boardGamePlayerPosition->boardGame);

        foreach ($boardGamePlayerPosition->boardGame->players as $player) {
            $bgPlayerCacheService->clearDetailCacheAllTypes($player);
        }
    }
}
