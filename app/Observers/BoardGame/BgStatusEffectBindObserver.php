<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\StatusEffectBind;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BgStatusEffectBindObserver
{
    private const CACHE_SERVICE = StatusEffectBind::CACHE_SERVICE;
    private const SERVICE = StatusEffectBind::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(StatusEffectBind $statusEffectBind)
    {
        $this->clearRelatedCache($statusEffectBind);

        $this->defaultObserverService->created(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(StatusEffectBind $statusEffectBind)
    {
        $this->clearRelatedCache($statusEffectBind);

        $this->defaultObserverService->updated(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(StatusEffectBind $statusEffectBind)
    {
        $this->clearRelatedCache($statusEffectBind);

        $this->defaultObserverService->deleted(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(StatusEffectBind $statusEffectBind)
    {
        $this->clearRelatedCache($statusEffectBind);

        $this->defaultObserverService->restored(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(StatusEffectBind $statusEffectBind)
    {
        $this->clearRelatedCache($statusEffectBind);

        $this->defaultObserverService->forceDeleted(
            $statusEffectBind,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($statusEffectBind)
    {
        $statusEffectBind->load('playerStatusEffect', 'boardGame', 'playerStatusEffect.player', 'playerStatusEffect.player.boardGame');

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearListCache();
        $bgPlayerCacheService->clearBgListCache($statusEffectBind->boardGame);

        foreach ($statusEffectBind->playerStatusEffect as $playerStatusEffect) {
            $bgPlayerCacheService->clearDetailCacheAllTypes($playerStatusEffect->player);
        }
    }
}
