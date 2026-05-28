<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardPositionEffect;
use App\Services\Observer\DefaultObserverService;

class BgPositionEffectObserver
{
    private const CACHE_SERVICE = BoardPositionEffect::CACHE_SERVICE;
    private const SERVICE = BoardPositionEffect::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardPositionEffect $boardPositionEffect)
    {
        $this->defaultObserverService->created(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardPositionEffect $boardPositionEffect)
    {
        $this->defaultObserverService->updated(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardPositionEffect $boardPositionEffect)
    {
        $this->defaultObserverService->deleted(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardPositionEffect $boardPositionEffect)
    {
        $this->defaultObserverService->restored(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardPositionEffect $boardPositionEffect)
    {
        $this->defaultObserverService->forceDeleted(
            $boardPositionEffect,
            self::CACHE_SERVICE
        );
    }
}
