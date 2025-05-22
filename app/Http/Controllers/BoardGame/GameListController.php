<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGameGameList;
use Illuminate\Http\Request;

class GameListController extends Controller
{
    public function list(Request $request, BoardGameGameList $BoardGameGameList)
    {
        return GameListResource::collection($BoardGameGameList::query()->where('board_game_id', $request->board_game_id)->get());
    }
}
