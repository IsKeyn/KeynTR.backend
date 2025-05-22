<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function getList() {
        $games = Game::query()->where('active', true)->where('show_in_list', true)->get();
        return GameResource::collection($games);
    }

    public function getGame(Request $request, Game $game) {
        if ($game->active) {
            ViewsLogService::set($request, get_class($game), $game->id);
            return GameResource::make($game);
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }
}
