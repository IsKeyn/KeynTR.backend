<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgItemRequest;
use App\Models\BoardGame\Item;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use App\Http\Resources\Admin\BoardGame\ItemResource;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = Item::class;
    private const CACHE_SERVICE = Item::CACHE_SERVICE;
    private const FILTER = Item::FILTER;
    private const DETAIL_RESOURCE = Item::DETAIL_RESOURCE;
    private const LIST_RESOURCE = Item::LIST_RESOURCE;
    private const SERVICE = Item::SERVICE;

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
            true,
            ['media'],
        );
    }

    public function store(BgItemRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgItemRequest $request, Item $item)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $item
        );
    }

    public function destroy(Item $item)
    {
        return $this->defaultAdminEntityService->destroy($item);
    }


    public function list(Item $Item)
    {
        return ItemResource::collection($Item::all());
    }
}
