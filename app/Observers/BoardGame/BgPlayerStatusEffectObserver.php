<?php

namespace App\Observers\BoardGame;

use App\Events\BoardGame\PlayerInfoForObs;
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
        $this->additionalActions($playerStatusEffect);

        $this->defaultObserverService->created(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(PlayerStatusEffect $playerStatusEffect)
    {
        $this->additionalActions($playerStatusEffect);

        $this->defaultObserverService->updated(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(PlayerStatusEffect $playerStatusEffect)
    {
        $this->additionalActions($playerStatusEffect);

        $this->defaultObserverService->deleted(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(PlayerStatusEffect $playerStatusEffect)
    {
        $this->additionalActions($playerStatusEffect);

        $this->defaultObserverService->restored(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(PlayerStatusEffect $playerStatusEffect)
    {
        $this->additionalActions($playerStatusEffect);

        $this->defaultObserverService->forceDeleted(
            $playerStatusEffect,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($playerStatusEffect)
    {
        $playerStatusEffect->load('boardGame', 'player', 'player.boardGame');

        // Отправляем данные через WS
        PlayerInfoForObs::dispatch($playerStatusEffect->player->user_id);

        $this->clearRelatedCache($playerStatusEffect);
    }

    private function clearRelatedCache($playerStatusEffect)
    {
        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearClientListCache($playerStatusEffect);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearListCache();
        $bgPlayerCacheService->clearBgListCache($playerStatusEffect->boardGame);
        $bgPlayerCacheService->clearDetailCacheAllTypes($playerStatusEffect->player);
    }
}
