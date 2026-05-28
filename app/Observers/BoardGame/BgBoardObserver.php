<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\Board;
use App\Services\Observer\DefaultObserverService;

class BgBoardObserver
{
    private const CACHE_SERVICE = Board::CACHE_SERVICE;
    private const SERVICE = Board::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Board $board)
    {
        $this->defaultObserverService->created(
            $board,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Board $board)
    {
        $this->defaultObserverService->updated(
            $board,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(Board $board)
    {
        $this->defaultObserverService->deleted(
            $board,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(Board $board)
    {
        $this->defaultObserverService->restored(
            $board,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(Board $board)
    {
        $this->defaultObserverService->forceDeleted(
            $board,
            self::CACHE_SERVICE
        );
    }
}
