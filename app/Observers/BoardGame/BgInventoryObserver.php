<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
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
        $boardGameInventory->load(['player', 'player.boardGame']);
        self::CACHE_SERVICE->clearClientPlayerListCache($boardGameInventory->player);

        $this->defaultObserverService->created(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGameInventory $boardGameInventory)
    {
        $boardGameInventory->load(['player', 'player.boardGame']);
        self::CACHE_SERVICE->clearClientPlayerListCache($boardGameInventory->player);

        $this->defaultObserverService->updated(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGameInventory $boardGameInventory)
    {
        $boardGameInventory->load(['player', 'player.boardGame']);
        self::CACHE_SERVICE->clearClientPlayerListCache($boardGameInventory->player);

        $this->defaultObserverService->deleted(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGameInventory $boardGameInventory)
    {
        $boardGameInventory->load(['player', 'player.boardGame']);
        self::CACHE_SERVICE->clearClientPlayerListCache($boardGameInventory->player);

        $this->defaultObserverService->restored(
            $boardGameInventory,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGameInventory $boardGameInventory)
    {
        $boardGameInventory->load(['player', 'player.boardGame']);
        self::CACHE_SERVICE->clearClientPlayerListCache($boardGameInventory->player);

        $this->defaultObserverService->forceDeleted(
            $boardGameInventory,
            self::CACHE_SERVICE
        );
    }
}
