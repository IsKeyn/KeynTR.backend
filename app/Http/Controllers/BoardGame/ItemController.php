<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Resources\BoardGame\ItemBindResource;
use App\Models\BoardGame\BoardGame;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\ItemResource;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\ItemBind;
use Illuminate\Support\Facades\Cache;

class ItemController extends Controller
{
    public function list(Item $item)
    {
        return ItemResource::collection($item::active()->get());
    }

    public function getList(
        $slug,
        BoardGame $BoardGame,
        ItemBind $ItemBind
    ) {
        $cacheKey = 'board_game_' . $slug . '_item_list_cache';
        $minutes = 60 * 24 * 30; // 30 дней

        return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $ItemBind, $slug) {
            $id = $BoardGame->findBySlug($slug)->value('id');

            $items = $ItemBind::active()->where('board_game_id', $id)->get();

            return ItemBindResource::collection($items);
        });
    }
}
