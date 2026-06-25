<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BgInventoryObserver
{
    private const CACHE_SERVICE = BoardGameInventory::CACHE_SERVICE;
    private const SERVICE = BoardGameInventory::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGameInventory $boardGameInventory)
    {
        $this->clearRelatedCache($boardGameInventory);

        $this->defaultObserverService->created(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGameInventory $boardGameInventory)
    {
        $this->clearRelatedCache($boardGameInventory);

        $this->defaultObserverService->updated(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGameInventory $boardGameInventory)
    {
        $this->clearRelatedCache($boardGameInventory);

        $this->defaultObserverService->deleted(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGameInventory $boardGameInventory)
    {
        $this->clearRelatedCache($boardGameInventory);

        $this->defaultObserverService->restored(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGameInventory $boardGameInventory)
    {
        $this->clearRelatedCache($boardGameInventory);

        $this->defaultObserverService->forceDeleted(
            $boardGameInventory,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($boardGameInventory)
    {
        $boardGameInventory->load(['player', 'player.boardGame']);

        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListCache($boardGameInventory->player);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearClientDetailCache($boardGameInventory->player);
    }
}
