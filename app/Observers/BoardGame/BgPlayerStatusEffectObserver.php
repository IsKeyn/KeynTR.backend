<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\PlayerStatusEffect;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BgPlayerStatusEffectObserver
{
    private const CACHE_SERVICE = PlayerStatusEffect::CACHE_SERVICE;
    private const SERVICE = PlayerStatusEffect::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(PlayerStatusEffect $playerStatusEffect)
    {
        $this->clearRelatedCache($playerStatusEffect);

        $this->defaultObserverService->created(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(PlayerStatusEffect $playerStatusEffect)
    {
        $this->clearRelatedCache($playerStatusEffect);

        $this->defaultObserverService->updated(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(PlayerStatusEffect $playerStatusEffect)
    {
        $this->clearRelatedCache($playerStatusEffect);

        $this->defaultObserverService->deleted(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(PlayerStatusEffect $playerStatusEffect)
    {
        $this->clearRelatedCache($playerStatusEffect);

        $this->defaultObserverService->restored(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(PlayerStatusEffect $playerStatusEffect)
    {
        $this->clearRelatedCache($playerStatusEffect);

        $this->defaultObserverService->forceDeleted(
            $playerStatusEffect,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($playerStatusEffect)
    {
        $playerStatusEffect->load('boardGame', 'player', 'player.boardGame');

        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientListCache($playerStatusEffect);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearListCache();
        $bgPlayerCacheService->clearBgListCache($playerStatusEffect->boardGame);
        $bgPlayerCacheService->clearDetailCacheAllTypes($playerStatusEffect->player);
    }
}
