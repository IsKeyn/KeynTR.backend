<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\Item;
use App\Models\BoardGame\ItemBind;
use App\Services\Cache\BoardGame\BgInventoryCacheService;
use App\Services\Cache\BoardGame\BgItemBindCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BgShopItemCacheService;
use App\Services\Observer\DefaultObserverService;
use Illuminate\Support\Facades\Cache;

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
        $this->additionalActions($item);

        $this->defaultObserverService->created(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Item $item)
    {
        $this->additionalActions($item);

        $this->defaultObserverService->updated(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(Item $item)
    {
        $this->additionalActions($item);

        $this->defaultObserverService->deleted(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(Item $item)
    {
        $this->additionalActions($item);

        $this->defaultObserverService->restored(
            $item,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(Item $item)
    {
        $this->additionalActions($item);

        $this->defaultObserverService->forceDeleted(
            $item,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($item)
    {
        $item->load(['itemBinds.boardGame', 'itemBinds.inventories.player']);
        $this->clearRelatedCache($item);
    }

    private function clearRelatedCache($item)
    {
        $bgItemBindCacheService = app(BgItemBindCacheService::class);
        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $bgInventoryCacheService = app(BgInventoryCacheService::class);
        $bgShopItemCacheService = app(BgShopItemCacheService::class);

        foreach ($item->itemBinds as $itemBinds) {
            if ($itemBinds->boardGame->id) {
                $bgItemBindCacheService->clearListCacheByBgId($itemBinds->boardGame->id);

                $cacheKey = $bgShopItemCacheService::LIST_PREFIX . '_' . $itemBinds->boardGame->slug . '_' . ItemBind::class;
                Cache::forget($cacheKey);
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
