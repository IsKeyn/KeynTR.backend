<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\Item;
use App\Services\Cache\BoardGame\BgInventoryCacheService;
use App\Services\Cache\BoardGame\BgItemBindCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
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
        $this->clearRelatedCache($item);

        $this->defaultObserverService->created(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Item $item)
    {
        $this->clearRelatedCache($item);

        $this->defaultObserverService->updated(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(Item $item)
    {
        $this->clearRelatedCache($item);

        $this->defaultObserverService->deleted(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(Item $item)
    {
        $this->clearRelatedCache($item);

        $this->defaultObserverService->restored(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(Item $item)
    {
        $this->clearRelatedCache($item);

        $this->defaultObserverService->forceDeleted(
            $item,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($item)
    {
        $item->load(['itemBinds.boardGame', 'itemBinds.inventories.player']);

        $bgItemBindCacheService = app(BgItemBindCacheService::class);
        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgInventoryCacheService = app(BgInventoryCacheService::class);

        foreach ($item->itemBinds as $itemBinds) {
            if ($itemBinds->boardGame->id) {
                $bgItemBindCacheService->clearListCacheByBgId($itemBinds->boardGame->id);
            }

            foreach ($itemBinds->inventories as $inventory) {
                if ($inventory->player) {
                    $bgPlayerCacheService->clearClientDetailCache($inventory->player);
                    $bgInventoryCacheService->clearClientPlayerListCache($inventory->player);
                }
            }
        }
    }
}
