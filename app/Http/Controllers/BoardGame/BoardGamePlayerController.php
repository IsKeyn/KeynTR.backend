<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\BoardGamePlayerFullResource;
use App\Http\Resources\BoardGame\BoardGamePlayerPositionsResource;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\LogResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use Illuminate\Http\Request;

class BoardGamePlayerController extends Controller
{
    public function get(
        $id,
        Request $request,
        BoardGamePlayer $BoardGamePlayer,
        BoardGameInventory $BoardGameInventory,
        BoardGameLog $BoardGameLog,
        BoardGamePlayerPosition $BoardGamePlayerPosition
    )
    {
        $player = $BoardGamePlayer->where('user_id', $id)->where('board_game_id', $request->board_game_id)->first();
//        $inventory = $BoardGamePlayer->inventory->where('board_game_id', $request->board_game_id);
        $inventory = $BoardGameInventory->where('user_id', $id)->where('board_game_id', $request->board_game_id)->get();
        $logs = $BoardGameLog->where('created_by', $id)->where('board_game_id', $request->board_game_id)->orderByDesc('id')->limit(100)->get();
        $steps = $BoardGamePlayerPosition->where('user_id', $id)->where('board_game_id', $request->board_game_id)->orderByDesc('id')->limit(100)->get();

        return [
            'player_info' => BoardGamePlayerFullResource::make($player),
            'inventory' => BoardGameInventoryResource::collection($inventory),
            'logs' => LogResource::collection($logs),
            'steps' => BoardGamePlayerPositionsResource::collection($steps),
        ];
    }

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

    public function updatedPoints (Request $request, BoardGamePlayer $BoardGamePlayer)
    {
        $user = $request->user();

        $currentPlayer = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->first();

        if ($currentPlayer) {
            $fields = [
                'points' => $request->points,
            ];

            return $currentPlayer->update($fields);
        }

        return false;
    }
}
