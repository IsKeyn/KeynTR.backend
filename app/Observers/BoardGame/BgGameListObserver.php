<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGameGameList;
use App\Services\Observer\DefaultObserverService;

class BgGameListObserver
{
    private const CACHE_SERVICE = BoardGameGameList::CACHE_SERVICE;
    private const SERVICE = BoardGameGameList::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGameGameList $boardGameGameList)
    {
        $this->defaultObserverService->created(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGameGameList $boardGameGameList)
    {
        $this->defaultObserverService->updated(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGameGameList $boardGameGameList)
    {
        $this->defaultObserverService->deleted(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGameGameList $boardGameGameList)
    {
        $this->defaultObserverService->restored(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGameGameList $boardGameGameList)
    {
        $this->defaultObserverService->forceDeleted(
            $boardGameGameList,
            self::CACHE_SERVICE
        );
    }
}
