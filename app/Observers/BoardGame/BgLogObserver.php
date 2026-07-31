<?php

namespace App\Observers\BoardGame;

use App\Events\BoardGame\ImportantLogs;
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
        $boardGameLog->load('boardGame');
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListCache($boardGameLog);

        $this->defaultObserverService->created(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        if ($boardGameLog->important) {
            $boardGameLog->load(['boardGame', 'user']);
            ImportantLogs::dispatch($boardGameLog);
        }
    }

    public function updated(BoardGameLog $boardGameLog)
    {
        $boardGameLog->load('boardGame');
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListCache($boardGameLog);

        $this->defaultObserverService->updated(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGameLog $boardGameLog)
    {
        $boardGameLog->load('boardGame');
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListCache($boardGameLog);

        $this->defaultObserverService->deleted(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGameLog $boardGameLog)
    {
        $boardGameLog->load('boardGame');
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListCache($boardGameLog);

        $this->defaultObserverService->restored(
            $boardGameLog,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGameLog $boardGameLog)
    {
        $boardGameLog->load('boardGame');
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientPlayerListCache($boardGameLog);

        $this->defaultObserverService->forceDeleted(
            $boardGameLog,
            self::CACHE_SERVICE
        );
    }
}
