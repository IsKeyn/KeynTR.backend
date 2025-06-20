<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\BoardGameItemResource;
use App\Http\Resources\BoardGame\BoardGameResource;
use App\Http\Resources\UserResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameItem;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\User;
use App\Services\TwitchService;
use Illuminate\Http\Request;

class BoardGameController extends Controller
{
    public function getBySlug($slug, Request $request, BoardGame $boardGame)
    {
        return BoardGameResource::make($boardGame->where('slug', $slug)->first());
    }

    public function getList(BoardGame $boardGame)
    {
        return BoardGameResource::collection($boardGame::all());
    }

    public function getItemAndInventory(Request $request, BoardGameItem $BoardGameItem, BoardGameInventory $BoardGameInventory)
    {
        $user = $request->user();

        if ($user) {
            $inventory = $BoardGameInventory->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->orderBy('updated_at', 'desc')->get();
        }

        $items = $BoardGameItem->where('board_game_id', $request->board_game_id)->get();

        return [
            'items' => BoardGameItemResource::collection($items),
            'inventory' => isset($inventory) ? BoardGameInventoryResource::collection($inventory) : '',
        ];
    }

    public function getStreamersOnline(Request $request)
    {
        if ($request->boardGameId) {
            $players = BoardGamePlayer::where('board_game_id', $request->boardGameId)->get();

            $twitchChannelsList = [];

            foreach ($players as $player) {
                if ($player->user && $player->user->additionalFields) {
                    foreach ($player->user->additionalFields as $field) {
                        if ($field->slug === 'twitch_channel') {
                            $path = parse_url($field->value, PHP_URL_PATH);
                            $twitchChannelsList[] = basename($path);
                        }
                    }
                }
            }

            $clientId = 'dub1gz76pv44mx1ojnyb9fvhe52m86';
            $clientSecret = '0uj93fwkcaq67q4uywkqzhjvw7idx2';

            $twitchService = new TwitchService();
            $token = $twitchService->getAccessToken($clientId, $clientSecret);

            $listOnline = [];

            foreach ($twitchChannelsList as $twitchName) {
                if ($twitchService->isStreamerLive($twitchName, $clientId, $token)) {
                    $listOnline[] = $twitchName;
                }
            }

            return $listOnline;
        }
    }

    public function getBoardInfo(Request $request)
    {
        $currentPlayerPosition = false;

        if ($user = $request->user()) {
            $currentPlayerPosition = BoardGamePlayerPosition::query()
                ->where('board_game_id', $request->board_game_id)
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')->first();
        }

        $positions = BoardGamePlayerPosition::query()->where('board_game_id', $request->board_game_id)->orderBy('id', 'desc')->get();

        $otherPlayerPosition = [];

        foreach ($positions as $position) {
            if ($user) {
                if ($user->id !== $position->user_id) {
                    if (!isset($otherPlayerPosition[$position->user_id])) {
                        $otherPlayerPosition[$position->user_id]['position'] = $position->position;
                        $otherPlayerPosition[$position->user_id]['info'] = UserResource::make(User::where('id', $position->user_id)->first());
                    }
                }
            } else {
                if (!isset($otherPlayerPosition[$position->user_id])) {
                    $otherPlayerPosition[$position->user_id]['position'] = $position->position;
                    $otherPlayerPosition[$position->user_id]['info'] = UserResource::make(User::where('id', $position->user_id)->first());
                }
            }
        }

        $returnData = [];

        if ($currentPlayerPosition) {
            $returnData['player']['position'] = $currentPlayerPosition->position;
        }

        if ($user) {
            $returnData['player']['info'] = UserResource::make($user);
        }

        $returnData['otherPlayers'] = $otherPlayerPosition;

        return $returnData;
    }
}
