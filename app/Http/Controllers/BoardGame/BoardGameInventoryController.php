<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\Media;
use App\Services\TagService;
use Illuminate\Http\Request;

class BoardGameInventoryController extends Controller
{
    public function index(BoardGameInventory $BoardGameInventory) {
        return $BoardGameInventory::all();
    }

    public function list(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventory = $BoardGameInventory->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->get();
            return BoardGameInventoryResource::collection($inventory);
        } else {
            return false;
        }
    }

    public function add(Request $request) {
        $fields = $request->validate([
            'board_game_id' => 'required',
            'board_game_item_id' => 'required',

        ]);

        $fields['user_id'] = $request->user()->id;
        $fields['created_by'] = $request->user()->id;

        $user = $request->user();

        if ($user) {
            if ($BoardGameInventory = BoardGameInventory::create($fields)) {
                return $BoardGameInventory;
            }
        }

        return false;
    }

    public function destroy(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventoryItem = $BoardGameInventory->where('id', $request->id)->where('user_id', $user->id)->where('board_game_id', $request->board_game_id);
            return $inventoryItem->delete();
        } else {
            return false;
        }
    }

    public function useItem(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventoryItem = $BoardGameInventory->where('id', $request->id)->where('user_id', $user->id)->where('board_game_id', $request->board_game_id);

            $fields = ['has_used' => true];

            return $inventoryItem->update($fields);
        } else {
            return false;
        }
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'description' => 'sometimes',
            'active' => 'sometimes',
            'image' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($BoardGameInventory = BoardGameInventory::create($fields)) {

            if (isset($fields['image'])) {
                $media = Media::query()->where('id', $fields['image'])->first();
                $BoardGameInventory->media()->syncWithPivotValues($media->id, ['type' => 1]);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($BoardGameInventory, $fields['tags']);
            }

            return $BoardGameInventory;
        }
    }

    public function update(Request $request, BoardGameInventory $BoardGameInventory) {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'description' => 'sometimes',
            'active' => 'sometimes',
            'image' => 'sometimes',
        ]);

        if (isset($fields['image'])) {
            $media = Media::query()->where('id', $fields['image'])->first();
            $BoardGameInventory->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($BoardGameInventory, $fields['tags']);
        }

        return $BoardGameInventory->update($fields);
    }

    public function edit(BoardGameInventory $BoardGameInventory)
    {
        return BoardGameInventoryResource::make($BoardGameInventory);
    }
}
