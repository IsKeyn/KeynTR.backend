<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgShopItemRequest;
use App\Http\Controllers\Controller;
use App\Models\BoardGame\ShopItem;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class BgShopItemController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = ShopItem::class;
    private const CACHE_SERVICE = ShopItem::CACHE_SERVICE;
    private const FILTER = ShopItem::FILTER;
    private const DETAIL_RESOURCE = ShopItem::DETAIL_RESOURCE;
    private const LIST_RESOURCE = ShopItem::LIST_RESOURCE;
    private const SERVICE = ShopItem::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            self::MODEL,
            self::CACHE_SERVICE,
            self::FILTER,
            self::LIST_RESOURCE,
            false,
        );
    }

    public function store(BgShopItemRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgShopItemRequest $request, ShopItem $shopItem)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $shopItem
        );
    }

    public function destroy(ShopItem $shopItem)
    {
        return $this->defaultAdminEntityService->destroy($shopItem);
    }
}
