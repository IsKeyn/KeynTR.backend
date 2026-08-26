<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardGameGameList;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use App\Services\Cache\GameCacheService;
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
        $this->additionalActions($boardGameGameList);

        $this->defaultObserverService->created(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardGameGameList $boardGameGameList)
    {
        $this->additionalActions($boardGameGameList);

        $this->defaultObserverService->updated(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardGameGameList $boardGameGameList)
    {
        $this->additionalActions($boardGameGameList);

        $this->defaultObserverService->deleted(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardGameGameList $boardGameGameList)
    {
        $this->additionalActions($boardGameGameList);

        $this->defaultObserverService->restored(
            $boardGameGameList,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardGameGameList $boardGameGameList)
    {
        $this->additionalActions($boardGameGameList);

        $this->defaultObserverService->forceDeleted(
            $boardGameGameList,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions(BoardGameGameList $boardGameGameList)
    {
        $boardGameGameList->load([
            'boardGame',
            'game',
            'playerGames',
            'playerGames.player',
            'playerGames.player.boardGame'
        ]);
        $this->clearRelatedCache($boardGameGameList);
    }

    private function clearRelatedCache(BoardGameGameList $boardGameGameList)
    {
        $bgPlayerGameCacheService = app(BgPlayerGameCacheService::class);

        foreach ($boardGameGameList->playerGames as $playerGame) {
            $bgPlayerGameCacheService->clearPlayerGameHistoryCache($playerGame->player);
        }

        $bgPlayerGameCacheService->clearClientDetailCache($boardGameGameList);

        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
    }
}
