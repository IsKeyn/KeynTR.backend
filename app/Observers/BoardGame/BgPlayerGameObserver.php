<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\PlayerGame;
use App\Services\Observer\DefaultObserverService;

class BgPlayerGameObserver
{
    private const CACHE_SERVICE = PlayerGame::CACHE_SERVICE;
    private const SERVICE = PlayerGame::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(PlayerGame $playerGame)
    {
        $this->clearRelatedCache($playerGame);

        $this->defaultObserverService->created(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(PlayerGame $playerGame)
    {
        $this->clearRelatedCache($playerGame);

        $this->defaultObserverService->updated(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(PlayerGame $playerGame)
    {
        $this->clearRelatedCache($playerGame);

        $this->defaultObserverService->deleted(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(PlayerGame $playerGame)
    {
        $this->clearRelatedCache($playerGame);

        $this->defaultObserverService->restored(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(PlayerGame $playerGame)
    {
        $this->clearRelatedCache($playerGame);

        $this->defaultObserverService->forceDeleted(
            $playerGame,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($playerGame)
    {
        $playerGame->load(['boardGame', 'player', 'user']);

        if ($playerGame->player) {
            $service = app(self::SERVICE);
            $service->clearPlayerGameHistoryCache($playerGame->player);
            $service->clearBgListCache($playerGame->boardGame);
        }
    }
}
