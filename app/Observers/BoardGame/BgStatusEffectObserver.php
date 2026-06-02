<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\StatusEffect;
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
        $this->defaultObserverService->created(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(StatusEffect $statusEffect)
    {
        $this->defaultObserverService->updated(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(StatusEffect $statusEffect)
    {
        $this->defaultObserverService->deleted(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(StatusEffect $statusEffect)
    {
        $this->defaultObserverService->restored(
            $statusEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(StatusEffect $statusEffect)
    {
        $this->defaultObserverService->forceDeleted(
            $statusEffect,
            self::CACHE_SERVICE
        );
    }
}
