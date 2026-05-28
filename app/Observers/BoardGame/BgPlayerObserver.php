<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGamePlayer;
use App\Services\Observer\DefaultObserverService;

class BgPlayerObserver
{
    private const CACHE_SERVICE = BoardGamePlayer::CACHE_SERVICE;
    private const SERVICE = BoardGamePlayer::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGamePlayer $boardGamePlayer)
    {
        $this->defaultObserverService->created(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGamePlayer $boardGamePlayer)
    {
        $this->defaultObserverService->updated(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGamePlayer $boardGamePlayer)
    {
        $this->defaultObserverService->deleted(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGamePlayer $boardGamePlayer)
    {
        $this->defaultObserverService->restored(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGamePlayer $boardGamePlayer)
    {
        $this->defaultObserverService->forceDeleted(
            $boardGamePlayer,
            self::CACHE_SERVICE
        );
    }
}
