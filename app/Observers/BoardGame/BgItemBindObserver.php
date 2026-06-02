<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\ItemBind;
use App\Services\Observer\DefaultObserverService;

class BgItemBindObserver
{
    private const CACHE_SERVICE = ItemBind::CACHE_SERVICE;
    private const SERVICE = ItemBind::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(ItemBind $itemBind)
    {
        $this->defaultObserverService->created(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(ItemBind $itemBind)
    {
        $this->defaultObserverService->updated(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(ItemBind $itemBind)
    {
        $this->defaultObserverService->deleted(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(ItemBind $itemBind)
    {
        $this->defaultObserverService->restored(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(ItemBind $itemBind)
    {
        $this->defaultObserverService->forceDeleted(
            $itemBind,
            self::CACHE_SERVICE
        );
    }
}
