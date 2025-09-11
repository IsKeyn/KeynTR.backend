<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\ItemBind;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\BoardGame\ItemBindResource;
use Illuminate\Support\Facades\Cache;

class ItemBindController extends Controller
{
    public function index(ItemBind $ItemBind) {
        return $ItemBind::all();
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

    public function store(Request $request)
    {
        $fields = $this->validateFields($request);

        $fields['created_by'] = $request->user()->id;

        $ItemBind = ItemBind::create($fields);
        $this->clearCache($ItemBind);
        return $ItemBind;
    }

    public function update(Request $request, ItemBind $ItemBind) {
        $fields = $this->validateFields($request);

        $this->clearCache($ItemBind);
        return $ItemBind->update($fields);
    }

    private function clearCache($item)
    {
        $slug = BoardGame::FindById($item->board_game_id)->value('slug');

        Cache::forget('board_game_' . $slug . '_item_list_cache');
    }

    public function edit(ItemBind $ItemBind)
    {
        return ItemBindResource::make($ItemBind);
    }

    public function destroy(ItemBind $ItemBind) {
        return $ItemBind->delete();
    }
}
