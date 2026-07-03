<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\ShopItem;
use App\Services\Observer\DefaultObserverService;

class BgShopItemObserver
{
    private const CACHE_SERVICE = ShopItem::CACHE_SERVICE;
    private const SERVICE = ShopItem::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(ShopItem $shopItem)
    {
        $this->clearRelatedCache($shopItem);

        $this->defaultObserverService->created(
            $shopItem,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(ShopItem $shopItem)
    {
        $this->clearRelatedCache($shopItem);

        $this->defaultObserverService->updated(
            $shopItem,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(ShopItem $shopItem)
    {
        $this->clearRelatedCache($shopItem);

        $this->defaultObserverService->deleted(
            $shopItem,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(ShopItem $shopItem)
    {
        $this->clearRelatedCache($shopItem);

        $this->defaultObserverService->restored(
            $shopItem,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(ShopItem $shopItem)
    {
        $this->clearRelatedCache($shopItem);

        $this->defaultObserverService->forceDeleted(
            $shopItem,
            self::CACHE_SERVICE
        );
    }

    private function clearRelatedCache($shopItem)
    {
        $shopItem->load([]);
    }
}
