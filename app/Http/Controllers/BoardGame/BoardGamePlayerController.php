<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\BoardGamePlayerFullResource;
use App\Http\Resources\BoardGame\BoardGamePlayerPositionsResource;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\BoardGamePlayerShortResource;
use App\Http\Resources\BoardGame\LogResource;
use App\Http\Resources\BoardGame\PlayerGameResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\Timer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BoardGamePlayerController extends Controller
{
    public function getByBoardGameSlugAndUserName (
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        $user = User::query()->where('name', $name)->first();

        if ($user) {
            $cacheKey = 'board_game_' . $slug . '_player_' . $user->id . '_cache';
            $minutes = 60 * 24 * 30; // 30 дней

            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGamePlayer, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $player = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $id)->first();

                return BoardGamePlayerFullResource::make($player);
            });
        }
    }

    public function getByBoardGameSlug(
        $slug,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        $user = Auth::user();

        if ($user) {
            $cacheKey = 'board_game_' . $slug . '_player_' . $user->id . '_cache';
            $minutes = 60 * 24 * 30; // 30 дней

            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGamePlayer, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $player = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $id)->first();

                return BoardGamePlayerFullResource::make($player);
            });
        }
    }

    public function listByBoardGameSlug(
        $slug,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        $cacheKey = 'board_game_' . $slug . '_player_list_cache';
        $minutes = 60 * 24 * 30; // 30 дней

        return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGamePlayer, $slug) {
            $boardGameId = $BoardGame->findBySlug($slug)->value('id');
            $players = $BoardGamePlayer->where('board_game_id', $boardGameId)->get();

            return BoardGamePlayerShortResource::collection($players);
        });
    }

    public function getInventoryByBoardGameSlugAndUserName(
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGameInventory $BoardGameInventory
    )
    {
        $user = User::query()->where('name', $name)->first();

        if ($user) {
            $cacheKey = 'board_game_' . $slug . '_player_inventory_' . $user->id . '_cache';
            $minutes = 60 * 24 * 30; // 30 дней

            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGameInventory, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $inventory = $BoardGameInventory
                    ->where('board_game_id', $id)
                    ->where('user_id', $user->id)->get();

                return BoardGameInventoryResource::collection($inventory);
            });
        }
    }

    public function getGamesByBoardGameSlugAndUserName(
        $slug,
        $name,
        BoardGame $BoardGame,
        PlayerGame $PlayerGame
    )
    {
        $user = User::query()->where('name', $name)->first();

        if ($user) {
            $cacheKey = 'board_game_' . $slug . '_player_games_' . $user->id . '_cache';
            $minutes = 60 * 24 * 30; // 30 дней

            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $PlayerGame, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $playerGames = PlayerGame::where('board_game_id', $id)
                    ->where('user_id', $user->id)
                    ->orderByDesc('id')->get();

                return PlayerGameResource::collection($playerGames);
            });
        }
    }

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

            if ($currentPlayer) {
                $boardGame = BoardGame::query()->where('id', $request->board_game_id)->first();

                $timerFields = [
                    'user_id' => $user->id,
                    'board_game_id' => $request->board_game_id,
                    'name' => $boardGame->name,
                    'limit' => 100*60*60,
                    'slug' => 'main',
                    'created_by' => $user->id,
                ];

                Timer::create($timerFields);
            }
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
