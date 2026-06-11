<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\PlayerStatusEffect;
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
        $this->defaultObserverService->created(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(PlayerStatusEffect $playerStatusEffect)
    {
        $this->defaultObserverService->updated(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(PlayerStatusEffect $playerStatusEffect)
    {
        $this->defaultObserverService->deleted(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(PlayerStatusEffect $playerStatusEffect)
    {
        $this->defaultObserverService->restored(
            $playerStatusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(PlayerStatusEffect $playerStatusEffect)
    {
        $this->defaultObserverService->forceDeleted(
            $playerStatusEffect,
            self::CACHE_SERVICE
        );
    }
}
