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

    public function update(BgItemBindRequest $request, ItemBind $ItemBind)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $ItemBind
        );
    }

    public function destroy(ItemBind $ItemBind)
    {
        return $this->defaultAdminEntityService->destroy($ItemBind);
    }

    public function list(ItemBind $ItemBind) {
        return ItemBindResource::collection($ItemBind::all());
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
//        $ItemBind = ItemBind::create($fields);
//        $this->clearCache($ItemBind);
//        return $ItemBind;
//    }
//
//    public function update(Request $request, ItemBind $ItemBind) {
//        $fields = $this->validateFields($request);
//
//        $this->clearCache($ItemBind);
//        return $ItemBind->update($fields);
//    }

    private function clearCache($item)
    {
        $slug = BoardGame::FindById($item->board_game_id)->value('slug');

        Cache::forget('board_game_' . $slug . '_item_list_cache');
    }
}
