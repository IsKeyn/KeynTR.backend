<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\StatusEffectBind;
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
        $this->defaultObserverService->created(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(StatusEffectBind $statusEffectBind)
    {
        $this->defaultObserverService->updated(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(StatusEffectBind $statusEffectBind)
    {
        $this->defaultObserverService->deleted(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(StatusEffectBind $statusEffectBind)
    {
        $this->defaultObserverService->restored(
            $statusEffectBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(StatusEffectBind $statusEffectBind)
    {
        $this->defaultObserverService->forceDeleted(
            $statusEffectBind,
            self::CACHE_SERVICE
        );
    }
}
