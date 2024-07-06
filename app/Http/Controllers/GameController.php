<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;

class GameController extends Controller
{
//    protected $resource = GameResource::class;

    public function getList() {
        $games = Game::query()->get();
        return GameResource::collection($games);
    }

    public function getGame(Request $request, Game $game) {
        ViewsLogService::set($request, get_class($game), $game->id);
        return GameResource::make($game);
    }
}
