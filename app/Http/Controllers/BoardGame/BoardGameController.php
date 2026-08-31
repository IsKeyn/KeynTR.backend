<?php

namespace App\Http\Controllers\BoardGame;

use App\Filters\BoardGame\BoardGameFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameForSelectResource;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\ItemBindResource;
use App\Http\Resources\BoardGame\BoardGameResource;
use App\Http\Resources\BoardGame\BoardGameShortResource;
use App\Http\Resources\User\UserResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\User;
use App\Services\BoardGame\BgPlayerService;
use App\Services\BoardGame\BoardGameService;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\TwitchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BoardGameController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function getLayoutData(Request $request)
    {
        $twitchService = app(TwitchService::class);

        return [
            'twitchOnline' => $twitchService->streamersLive(),
            'boardGame' => BoardGameService::getBySlug($request->slug),
            'player' => BgPlayerService::getCurrent($request->slug),
        ];
    }

    public function getList(BoardGame $boardGame)
    {
        $boardGameList = $boardGame::query()
            ->active()
            ->where('is_test', false)
            ->orderBy('started_at', 'desc')
            ->get();

        return BoardGameShortResource::collection($boardGameList);
    }

    /**
     * Короткий список ивентов, для выбора из селекта
     *
     * @param Request $request
     * @return mixed
     */
    public function getShortList(Request $request)
    {
        $cacheKey = BoardGameCacheService::LIST_PREFIX . '_short';

        if (!$request->fullList) {
            $cacheKey .= '_' . $request->page . '_' . $request->perPage;
        }

        $time = BoardGameCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                BoardGameCacheService::LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = BoardGameCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request) {
            $filter = new BoardGameFilter($request);
            $boardGames = $filter->apply(BoardGame::query())->select('id', 'name')->active();

            if (!isset($request->sort)) {
                $boardGames->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $request->fullList ? $boardGames->get() : $boardGames->paginate($request->perPage ? $request->perPage : 10);

            return BoardGameForSelectResource::collection($result);
        });
    }


    public function getBySlug($slug, Request $request, BoardGame $boardGame)
    {
        // TODO устаревший метод, удалить когда более не будет использоваться
        $validated = $request->validate([
            'type' => 'sometimes|string',
        ]);

        if (isset($validated['type'])) {
            $cacheKey = 'board_game_' . $slug . '_' . $validated['type'] . '_cache';
            $minutes = 60 * 24 * 30; // 30 дней

            if ($validated['type'] === 'short') {
//                return Cache::remember($cacheKey, $minutes, function () use ($slug, $request, $boardGame) {
                    $boardGame = $boardGame->where('slug', $slug)->where('active', true)->first();

                    if ($boardGame) {
                        $player = null;

                        if ($user = $request->user()) {
                            $player = BoardGamePlayer::query()->where('user_id', $user->id)->where('board_game_id',
                                $boardGame->id)->first();
                        }

                        return BoardGameShortResource::make($boardGame, $player);
                    }
//                });
            }
        } else {
            /* Используется в старой версии игры */
            $boardGame = $boardGame->where('slug', $slug)->where('active', true)->first();

            if ($boardGame) {
                return BoardGameResource::make($boardGame);
            }
        }
    }

    public function getItemAndInventory(Request $request, ItemBind $BoardGameItem, BoardGameInventory $BoardGameInventory)
    {
        $user = $request->user();

        if ($user) {
            $inventory = $BoardGameInventory->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->orderBy('updated_at', 'desc')->get();
        }

        $items = $BoardGameItem->where('board_game_id', $request->board_game_id)->get();

        return [
            'items' => ItemBindResource::collection($items),
            'inventory' => isset($inventory) ? BoardGameInventoryResource::collection($inventory) : '',
        ];
    }

    public function getStreamersOnline(Request $request)
    {
        if ($request->boardGameId) {
            $players = BoardGamePlayer::active()->where('board_game_id', $request->boardGameId)->get();

            $twitchChannelsList = [];

            foreach ($players as $player) {
                if ($player->user && $player->user->additionalFields) {
                    foreach ($player->user->additionalFields as $field) {
                        if ($field->slug === 'twitch_channel') {
                            $path = parse_url($field->value, PHP_URL_PATH);
                            $twitchChannelsList[$player->user->id] = basename($path);
                        }
                    }
                }
            }

            $clientId = config('twitch.client_id');
            $clientSecret = config('twitch.client_secret');

            $twitchService = new TwitchService();
            $token = $twitchService->getAccessToken($clientId, $clientSecret);

            $listOnline = [];

            foreach ($twitchChannelsList as $userId => $twitchName) {
                if ($twitchService->isStreamerLive($twitchName, $clientId, $token)) {
                    $listOnline[$userId] = $twitchName;
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
