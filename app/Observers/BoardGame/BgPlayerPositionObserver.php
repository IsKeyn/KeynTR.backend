<?php

namespace App\Observers\BoardGame;

use App\Events\BoardGame\PlayerInfoForObs;
use App\Events\MovePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
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
        $this->additionalActions($boardGamePlayerPosition);

        $this->defaultObserverService->created(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        /* Отправляем данные для движения игрока */
        $moveData = [
            'playerId' => $boardGamePlayerPosition->bg_player_id,
            'positionData' => [
                'firstPosition' => [
                    'position' => $boardGamePlayerPosition->position,
                ],
                'finalPosition' => [
                    'position' => $boardGamePlayerPosition->position,
                ],
            ],
        ];

        MovePlayer::dispatch($moveData);
    }

    public function updated(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->additionalActions($boardGamePlayerPosition);

        $this->defaultObserverService->updated(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->additionalActions($boardGamePlayerPosition);

        $this->defaultObserverService->deleted(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->additionalActions($boardGamePlayerPosition);

        $this->defaultObserverService->restored(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        $this->additionalActions($boardGamePlayerPosition);

        $this->defaultObserverService->forceDeleted(
            $boardGamePlayerPosition,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($boardGamePlayerPosition)
    {
        $boardGamePlayerPosition->load(['boardGame', 'boardGame.players', 'player']);

        // Отправляем данные через WS
        PlayerInfoForObs::dispatch($boardGamePlayerPosition->player);

        $this->clearRelatedCache($boardGamePlayerPosition);
    }

    private function clearRelatedCache($boardGamePlayerPosition)
    {
        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearBgListCache($boardGamePlayerPosition->boardGame);

        foreach ($boardGamePlayerPosition->boardGame->players as $player) {
            $bgPlayerCacheService->clearDetailCacheAllTypes($player);
        }
    }
}
