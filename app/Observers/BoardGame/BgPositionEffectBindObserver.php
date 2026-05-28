<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Services\Observer\DefaultObserverService;

class BgPositionEffectBindObserver
{
    private const CACHE_SERVICE = BoardPositionEffectsBind::CACHE_SERVICE;
    private const SERVICE = BoardPositionEffectsBind::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        $this->defaultObserverService->created(
            $boardPositionEffectsBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        $this->defaultObserverService->updated(
            $boardPositionEffectsBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        $this->defaultObserverService->deleted(
            $boardPositionEffectsBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        $this->defaultObserverService->restored(
            $boardPositionEffectsBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        $this->defaultObserverService->forceDeleted(
            $boardPositionEffectsBind,
            self::CACHE_SERVICE
        );
    }
}
