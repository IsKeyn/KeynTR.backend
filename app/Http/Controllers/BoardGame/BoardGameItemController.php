<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BoardGame\BoardGameItemResource;
use App\Models\BoardGame\BoardGameItem;
use App\Models\Media;
use App\Services\TagService;
use Illuminate\Http\Request;

class BoardGameItemController extends Controller
{
    public function index(BoardGameItem $BoardGameItem) {
        return $BoardGameItem::all();
    }

    public function list(Request $request, BoardGameItem $BoardGameItem) {
        return BoardGameItemResource::collection($BoardGameItem::all());
    }

    public function validateFields($request) {
        $validated = $request->validate([
                'name' => 'required',
                'slug' => 'sometimes',
                'description' => 'sometimes',
                'actions' => 'sometimes',
                'type' => 'sometimes',
                'board_game_id' => 'sometimes',
                'active' => 'sometimes',
                'image' => 'sometimes',
            ]);

        $validated['type'] = $validated['type'] ? $validated['type'] : 0;

        return $validated;
    }

    public function store(Request $request)
    {
        $fields = $this->validateFields($request);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($BoardGameItem = BoardGameItem::create($fields)) {

            if (isset($fields['image'])) {
                $media = Media::query()->where('id', $fields['image'])->first();
                $BoardGameItem->media()->syncWithPivotValues($media->id, ['type' => 1]);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($BoardGameItem, $fields['tags']);
            }

            return $BoardGameItem;
        }
    }

    public function update(Request $request, BoardGameItem $BoardGameItem) {
        $fields = $this->validateFields($request);

        if (isset($fields['image'])) {
            $media = Media::query()->where('id', $fields['image'])->first();
            $BoardGameItem->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($BoardGameItem, $fields['tags']);
        }

        return $BoardGameItem->update($fields);
    }

    public function edit(BoardGameItem $BoardGameItem)
    {
        return BoardGameItemResource::make($BoardGameItem);
    }
}
