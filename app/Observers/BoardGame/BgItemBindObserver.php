<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\ItemBind;
use App\Services\Cache\BoardGame\BgInventoryCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
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
        $this->clearRelatedCache($itemBind);

        $this->defaultObserverService->created(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(ItemBind $itemBind)
    {
        $this->clearRelatedCache($itemBind);

        $this->defaultObserverService->updated(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(ItemBind $itemBind)
    {
        $this->clearRelatedCache($itemBind);

        $this->defaultObserverService->deleted(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(ItemBind $itemBind)
    {
        $this->clearRelatedCache($itemBind);

        $this->defaultObserverService->restored(
            $itemBind,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(ItemBind $itemBind)
    {
        $this->clearRelatedCache($itemBind);

        $this->defaultObserverService->forceDeleted(
            $itemBind,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($itemBind)
    {
        $itemBind->load(['boardGame', 'inventories.player']);

        $cacheService = app(self::CACHE_SERVICE);
        $cacheService->clearListCacheByBgId($itemBind->boardGame->id);

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgInventoryCacheService = app(BgInventoryCacheService::class);

        foreach ($itemBind->inventories as $inventory) {
            if ($inventory->player) {
                $bgPlayerCacheService->clearClientDetailCache($inventory->player);
                $bgInventoryCacheService->clearClientPlayerListCache($inventory->player);
            }
        }
    }
}
