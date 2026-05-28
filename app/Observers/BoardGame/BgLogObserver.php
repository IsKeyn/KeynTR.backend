<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGameLog;
use App\Services\Observer\DefaultObserverService;

class BgLogObserver
{
    private const CACHE_SERVICE = BoardGameLog::CACHE_SERVICE;
    private const SERVICE = BoardGameLog::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGameLog $boardGameLog)
    {
        $this->defaultObserverService->created(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGameLog $boardGameLog)
    {
        $this->defaultObserverService->updated(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGameLog $boardGameLog)
    {
        $this->defaultObserverService->deleted(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGameLog $boardGameLog)
    {
        $this->defaultObserverService->restored(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGameLog $boardGameLog)
    {
        $this->defaultObserverService->forceDeleted(
            $boardGameLog,
            self::CACHE_SERVICE
        );
    }
}
