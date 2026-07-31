<?php

namespace App\Observers\BoardGame;

use App\Events\BoardGame\PlayerInfoForObs;
use App\Events\PlayerData;
use App\Models\BoardGame\BoardGamePlayer;
use App\Services\Cache\BoardGame\BoardGameCacheService;
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
        $boardGamePlayer->load(['boardGame', 'user', 'user.bgPlayer', 'user.bgPlayer.boardGame']);
        $this->additionalActions($boardGamePlayer);

        $this->defaultObserverService->created(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        PlayerData::dispatch($boardGamePlayer);
    }

    public function updated(BoardGamePlayer $boardGamePlayer)
    {
        $boardGamePlayer->load(['boardGame', 'user', 'user.bgPlayer', 'user.bgPlayer.boardGame']);
        $this->additionalActions($boardGamePlayer);

        $this->defaultObserverService->updated(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        PlayerData::dispatch($boardGamePlayer);
    }

    public function deleted(BoardGamePlayer $boardGamePlayer)
    {
        $boardGamePlayer->load(['boardGame', 'user', 'user.bgPlayer', 'user.bgPlayer.boardGame']);
        $this->clearRelatedCache($boardGamePlayer);

        $this->defaultObserverService->deleted(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        PlayerData::dispatch($boardGamePlayer);
    }

    public function restored(BoardGamePlayer $boardGamePlayer)
    {
        $boardGamePlayer->load(['boardGame', 'user', 'user.bgPlayer', 'user.bgPlayer.boardGame']);
        $this->additionalActions($boardGamePlayer);

        $this->defaultObserverService->restored(
            $boardGamePlayer,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        PlayerData::dispatch($boardGamePlayer);
    }

    public function forceDeleted(BoardGamePlayer $boardGamePlayer)
    {
        $boardGamePlayer->load(['boardGame', 'user', 'user.bgPlayer', 'user.bgPlayer.boardGame']);
        $this->additionalActions($boardGamePlayer);

        $this->defaultObserverService->forceDeleted(
            $boardGamePlayer,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($boardGamePlayer)
    {
        // Делаем перерасчет мест
        if ($boardGamePlayer->boardGame) {
            $service = app(self::SERVICE);
            $service->recalculatePlaces($boardGamePlayer->boardGame->id);
        }

        // Пересчитываем места игроков
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearBgListCache($boardGamePlayer->boardGame);

        // Сбрасываем кеш зависимых сущностей
        $this->clearRelatedCache($boardGamePlayer);

        // Отправляем данные через WS
        PlayerInfoForObs::dispatch($boardGamePlayer);
    }

    private function clearRelatedCache($boardGamePlayer)
    {
        /* Сбрасываем кеш, списка настольных игр, в которых участвует игрок в каждой настольной игре */
        $boardGameCacheService = app(BoardGameCacheService::class);

        foreach ($boardGamePlayer->user->bgPlayer as $player) {
            $boardGameCacheService->clearDetailCacheAllTypes($player->boardGame);
            $boardGameCacheService->clearClientPlayerListCacheFn($player->boardGame->slug, $player->user_id);
        }
    }
}
