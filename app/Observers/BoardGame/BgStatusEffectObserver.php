<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\StatusEffect;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Observer\DefaultObserverService;

class BgStatusEffectObserver
{
    private const CACHE_SERVICE = StatusEffect::CACHE_SERVICE;
    private const SERVICE = StatusEffect::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(StatusEffect $statusEffect)
    {
        $this->clearRelatedCache($statusEffect);

        $this->defaultObserverService->created(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(StatusEffect $statusEffect)
    {
        $this->clearRelatedCache($statusEffect);

        $this->defaultObserverService->updated(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(StatusEffect $statusEffect)
    {
        $this->clearRelatedCache($statusEffect);

        $this->defaultObserverService->deleted(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(StatusEffect $statusEffect)
    {
        $this->clearRelatedCache($statusEffect);

        $this->defaultObserverService->restored(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(StatusEffect $statusEffect)
    {
        $this->clearRelatedCache($statusEffect);

        $this->defaultObserverService->forceDeleted(
            $statusEffect,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($statusEffect)
    {
        $statusEffect->load([
            'statusEffectBinds',
            'statusEffectBinds.playerStatusEffect',
            'statusEffectBinds.playerStatusEffect.player',
            'statusEffectBinds.playerStatusEffect.player.boardGame',
            'boardGame'
        ]);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgPlayerCacheService->clearListCache();
        $bgPlayerCacheService->clearBgListCache($statusEffect->boardGame);

        foreach ($statusEffect->statusEffectBind as $statusEffectBind) {
            foreach ($statusEffectBind->playerStatusEffect as $playerStatusEffect) {
                $bgPlayerCacheService->clearDetailCacheAllTypes($playerStatusEffect->player);
            }
        }
    }
}
