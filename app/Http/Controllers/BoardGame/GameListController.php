<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;

use App\Http\Resources\BoardGame\games\GameListShortResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GameListController extends Controller
{
    public function list(Request $request, BoardGameGameList $BoardGameGameList)
    {
        $cacheKey = 'bg_game_list_cache_' . $request->slug;
        $minutes = 10080; // 7 дней в минутах

        return Cache::remember($cacheKey, $minutes, function () use ($request, $BoardGameGameList) {
            $boardGame = BoardGame::findBySlug($request->slug)->active()->first();

            if ($boardGame) {
                return GameListShortResource::collection($BoardGameGameList::query()
                    ->findByBoardGame($boardGame->id)
                    ->active()
                    ->with([
                        'game',
                        'platform',
                        'addedBy',
                        'game.platforms',
                        'game.dates',
                        'game.anonsDates',
                        'game.tags',
                        'game.groups',
                        'game.genres',
                        'game.company',
                        'game.link',
                    ])
                    ->get()
                )->resolve();
            }
        });
    }
}
