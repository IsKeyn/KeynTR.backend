<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\PlayerInteractions;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BgPlayerInteractionsObserver
{
    private const CACHE_SERVICE = PlayerInteractions::CACHE_SERVICE;
    private const SERVICE = PlayerInteractions::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(PlayerInteractions $playerInteractions)
    {
        $this->clearRelatedCache($playerInteractions);

        $this->defaultObserverService->created(
            $playerInteractions,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(PlayerInteractions $playerInteractions)
    {
        $this->clearRelatedCache($playerInteractions);

        $this->defaultObserverService->updated(
            $playerInteractions,
            self::CACHE_SERVICE,
            self::SERVICE,
            false,
        );
    }

    public function deleted(PlayerInteractions $playerInteractions)
    {
        $this->clearRelatedCache($playerInteractions);

        $this->defaultObserverService->deleted(
            $playerInteractions,
            self::CACHE_SERVICE,
            self::SERVICE,
            false,
        );
    }

    public function restored(PlayerInteractions $playerInteractions)
    {
        $this->clearRelatedCache($playerInteractions);

        $this->defaultObserverService->restored(
            $playerInteractions,
            self::CACHE_SERVICE,
            self::SERVICE,
            false,
        );
    }

    public function forceDeleted(PlayerInteractions $playerInteractions)
    {
        $this->clearRelatedCache($playerInteractions);

        $this->defaultObserverService->forceDeleted(
            $playerInteractions,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($playerInteractions)
    {
        $playerInteractions->load(['player', 'boardGame']);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearBgListCache($playerInteractions->boardGame);
        $bgPlayerCacheService->clearDetailCacheAllTypes($playerInteractions->player);
    }
}
