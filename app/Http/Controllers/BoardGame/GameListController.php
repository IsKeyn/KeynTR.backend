<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use Illuminate\Http\Request;

class GameListController extends Controller
{
    public function list(Request $request, BoardGameGameList $BoardGameGameList)
    {
        $boardGame = BoardGame::findBySlug($request->slug)->active()->first();

        if ($boardGame) {
            return GameListResource::collection($BoardGameGameList::query()->findByBoardGame($boardGame->id)->active()->get());
        }
    }
}
