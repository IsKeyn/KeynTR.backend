<?php

namespace App\Observers\BoardGame;

use App\Events\BoardGame\PlayerInfoForObs;
use App\Models\BoardGame\PlayerGame;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
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
        $this->additionalActions($playerGame);

        $this->defaultObserverService->created(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(PlayerGame $playerGame)
    {
        $this->additionalActions($playerGame);

        $this->defaultObserverService->updated(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(PlayerGame $playerGame)
    {
        $this->additionalActions($playerGame);

        $this->defaultObserverService->deleted(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(PlayerGame $playerGame)
    {
        $this->additionalActions($playerGame);

        $this->defaultObserverService->restored(
            $playerGame,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(PlayerGame $playerGame)
    {
        $this->additionalActions($playerGame);

        $this->defaultObserverService->forceDeleted(
            $playerGame,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions(PlayerGame $playerGame)
    {
        $playerGame->load(['boardGame', 'player', 'user']);

        // Отправляем данные через WS
        PlayerInfoForObs::dispatch($playerGame->player);

        $this->clearRelatedCache($playerGame);
    }

    private function clearRelatedCache($playerGame)
    {
        $service = app(self::CACHE_SERVICE);
        $service->clearClientDetailCache($playerGame);
        $service->clearActionsWithGameList($playerGame);

        if ($playerGame->player) {
            $service->clearPlayerGameHistoryCache($playerGame->player);

        }

        $bgPlayerCacheService = app(BgPlayerCacheService::class);

        if ($playerGame->boardGame) {
            $bgPlayerCacheService->clearBgListCache($playerGame->boardGame);
        }
    }
}
