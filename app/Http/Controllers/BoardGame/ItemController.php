<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Resources\BoardGame\Items\BgItemResource;
use App\Models\BoardGame\BoardGame;
use App\Http\Controllers\Controller;
use App\Models\BoardGame\Item;
use App\Services\BoardGame\ItemService;

class ItemController extends Controller
{
    public function list(Item $item)
    {
        return BgItemResource::collection($item::active()->orderBy('id', 'desc')->get());
    }

    public function getList(
        $slug,
        BoardGame $BoardGame
    ) {
        $bgId = $BoardGame->findBySlug($slug)->value('id');
        return ItemService::itemsInBoardGame($bgId);
    }
}
