<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGamePlayerPosition;
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
        $this->defaultObserverService->created(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->defaultObserverService->updated(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->defaultObserverService->deleted(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->defaultObserverService->restored(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->defaultObserverService->forceDeleted(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE
        );
    }
}
