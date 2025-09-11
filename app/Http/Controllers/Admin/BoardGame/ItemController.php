<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\Item;
use App\Models\Media;
use App\Http\Controllers\Controller;
use App\Services\TagService;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\BoardGame\ItemResource;
use Illuminate\Support\Facades\Cache;

class ItemController extends Controller
{
    public function index(Item $Item)
    {
        return $Item::all();
    }

    public function list(Item $Item)
    {
        return ItemResource::collection($Item::all());
    }

    public function validateFields($request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'short_description' => 'sometimes',
            'full_description' => 'sometimes',
            'actions' => 'sometimes',
            'type' => 'sometimes',
            'active' => 'sometimes',
            'author' => 'sometimes',
            'created_by' => 'sometimes',
            'image' => 'sometimes',
        ]);

        $validated['type'] = $validated['type'] ? $validated['type'] : 0;

        return $validated;
    }

    public function store(Request $request)
    {
        $fields = $this->validateFields($request);

        $fields['created_by'] = $request->user()->id;

        if ($Item = Item::create($fields)) {
            if (isset($fields['image'])) {
                $media = Media::query()->where('id', $fields['image'])->first();
                $Item->media()->syncWithPivotValues($media->id, ['type' => 1]);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($Item, $fields['tags']);
            }

            $this->clearCache();
            return $Item;
        }
    }

    public function update(Request $request, Item $Item)
    {
        $fields = $this->validateFields($request);

        if (isset($fields['image'])) {
            $media = Media::query()->where('id', $fields['image'])->first();
            $Item->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($Item, $fields['tags']);
        }

        $this->clearCache();
        return $Item->update($fields);
    }

    private function clearCache()
    {
        $boardGames = BoardGame::all();

        foreach ($boardGames as $boardGame) {
            Cache::forget('board_game_' . $boardGame->slug . '_item_list_cache');
        }
    }

    public function edit(Item $Item)
    {
        return ItemResource::make($Item);
    }

    public function destroy(Item $Item)
    {
        return $Item->delete();
    }
}
