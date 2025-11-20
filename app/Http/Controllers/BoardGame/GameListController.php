<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GameListController extends Controller
{
    public function list(Request $request, BoardGameGameList $BoardGameGameList)
    {
        $cacheKey = 'bg_game_list_cache_' . $request->slug;
        $minutes = 1440; // 24 часа в минутах

        return Cache::remember($cacheKey, $minutes, function () use ($request, $BoardGameGameList) {
            $boardGame = BoardGame::findBySlug($request->slug)->active()->first();

            if ($boardGame) {
                return GameListResource::collection($BoardGameGameList::query()->findByBoardGame($boardGame->id)->active()->get());
            }
        });
    }
}
