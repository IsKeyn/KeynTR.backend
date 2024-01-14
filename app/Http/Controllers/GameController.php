<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
//    protected $resource = GameResource::class;

    public function getList() {
        $games = Game::query()->get();
        return GameResource::collection($games);
    }

    public function getGame($query, Request $request) {
        $game = Game::query()->where('slug', $query)->first();

        if ($game) {
            return GameResource::make($game);
        }
    }
}
