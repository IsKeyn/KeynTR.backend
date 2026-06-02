<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\Item;
use App\Services\Observer\DefaultObserverService;

class BgItemObserver
{
    private const CACHE_SERVICE = Item::CACHE_SERVICE;
    private const SERVICE = Item::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Item $item)
    {
        $this->defaultObserverService->created(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Item $item)
    {
        $this->defaultObserverService->updated(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(Item $item)
    {
        $this->defaultObserverService->deleted(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(Item $item)
    {
        $this->defaultObserverService->restored(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(Item $item)
    {
        $this->defaultObserverService->forceDeleted(
            $item,
            self::CACHE_SERVICE
        );
    }
}
