<?php

namespace App\Observers\BoardGame;

use App\Events\PlayerData;
use App\Models\BoardGame\AddGame;
use App\Services\Observer\DefaultObserverService;

class BgAddGameObserver
{
    private const CACHE_SERVICE = AddGame::CACHE_SERVICE;
    private const SERVICE = AddGame::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(AddGame $addGame)
    {
        $this->additionalActions($addGame);

        $this->defaultObserverService->created(
            $addGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(AddGame $addGame)
    {
        $this->additionalActions($addGame);

        $this->defaultObserverService->updated(
            $addGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(AddGame $addGame)
    {
        $this->clearRelatedCache($addGame);

        $this->defaultObserverService->deleted(
            $addGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(AddGame $addGame)
    {
        $this->additionalActions($addGame);

        $this->defaultObserverService->restored(
            $addGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(AddGame $addGame)
    {
        $this->additionalActions($addGame);

        $this->defaultObserverService->forceDeleted(
            $addGame,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($addGame)
    {
    }

    private function clearRelatedCache($addGame)
    {
    }
}
