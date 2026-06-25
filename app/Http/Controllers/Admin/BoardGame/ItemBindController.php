<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgItemBindRequest;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\ItemBind;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use App\Http\Resources\Admin\BoardGame\ItemBindResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ItemBindController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = ItemBind::class;
    private const CACHE_SERVICE = ItemBind::CACHE_SERVICE;
    private const FILTER = ItemBind::FILTER;
    private const DETAIL_RESOURCE = ItemBind::DETAIL_RESOURCE;
    private const LIST_RESOURCE = ItemBind::LIST_RESOURCE;
    private const SERVICE = ItemBind::SERVICE;

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

    public function store(BgItemBindRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgItemBindRequest $request, ItemBind $itemBind)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $itemBind
        );
    }

    public function destroy(ItemBind $itemBind)
    {
        return $this->defaultAdminEntityService->destroy($itemBind);
    }

    public function list(ItemBind $itemBind) {
        return ItemBindResource::collection($itemBind::all());
    }

    public function validateFields($request) {
        $validated = $request->validate([
            'item_id' => 'required',
            'board_game_id' => 'sometimes',
            'active' => 'sometimes',
            'created_by' => 'sometimes',
        ]);

        return $validated;
    }

//    public function store(Request $request)
//    {
//        $fields = $this->validateFields($request);
//
//        $fields['created_by'] = $request->user()->id;
//
//        $itemBind = ItemBind::create($fields);
//        $this->clearCache($itemBind);
//        return $itemBind;
//    }
//
//    public function update(Request $request, ItemBind $itemBind) {
//        $fields = $this->validateFields($request);
//
//        $this->clearCache($itemBind);
//        return $itemBind->update($fields);
//    }

    private function clearCache($item)
    {
        $slug = BoardGame::FindById($item->board_game_id)->value('slug');

        Cache::forget('board_game_' . $slug . '_item_list_cache');
    }
}
