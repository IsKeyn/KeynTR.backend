<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardPositionEffect;
use App\Services\Cache\BoardGame\BoardGameCacheService;
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
        $this->additionalActions($boardPositionEffect);

        $this->defaultObserverService->created(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(BoardPositionEffect $boardPositionEffect)
    {
        $this->additionalActions($boardPositionEffect);

        $this->defaultObserverService->updated(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(BoardPositionEffect $boardPositionEffect)
    {
        $this->additionalActions($boardPositionEffect);

        $this->defaultObserverService->deleted(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(BoardPositionEffect $boardPositionEffect)
    {
        $this->additionalActions($boardPositionEffect);

        $this->defaultObserverService->restored(
            $boardPositionEffect,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(BoardPositionEffect $boardPositionEffect)
    {
        $this->additionalActions($boardPositionEffect);

        $this->defaultObserverService->forceDeleted(
            $boardPositionEffect,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($boardPositionEffect)
    {
        /* Сбрасываем кеш зависимых сущностей */
        $this->clearRelatedCache($boardPositionEffect);
    }

    private function clearRelatedCache($boardPositionEffect)
    {
        $boardPositionEffect->load(['boardPositionEffectBinds.boardGame']);

        $boardGameCacheService = app(BoardGameCacheService::class);

        foreach ($boardPositionEffect->boardPositionEffectBinds as $boardPositionEffectBinds) {
            $boardGameCacheService->clearDetailCacheAllTypes($boardPositionEffectBinds->boardGame);
        }
    }
}
