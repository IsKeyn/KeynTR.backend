<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Models\BoardGame\BoardGamePlayer;
use Illuminate\Http\Request;

class BoardGamePlayerController extends Controller
{
    public function list(Request $request, BoardGamePlayer $BoardGamePlayer)
    {
        $players = $BoardGamePlayer->where('board_game_id', $request->board_game_id)->first();

        return BoardGamePlayerResource::collection($players);
    }

    public function add(Request $request, BoardGamePlayer $BoardGamePlayer)
    {
        $user = $request->user();

        $currentPlayer = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->first();

        if (!$currentPlayer) {
            $fields = [
                'user_id' => $user->id,
                'board_game_id' => $request->board_game_id,
                'created_by' => $user->id,
            ];

            $currentPlayer = $BoardGamePlayer::create($fields);
        }

        return BoardGamePlayerResource::make($currentPlayer);
    }
}
