<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\Games\BgGameResource;
use App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameShortResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\PlayerGame;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BgGameController extends Controller
{
    /**
     * @param $slug
     * @param $gameSlug
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function get(
        $slug,
        $gameSlug
    )
    {
        if (!$slug) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        if (!$gameSlug) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerGameCacheService::DETAIL_PREFIX . '_' . $slug . '_' . $gameSlug;

        return Cache::remember($cacheKey, BgPlayerGameCacheService::TIME, function () use ($slug, $gameSlug) {
            $boardGame = BoardGame::query()
                ->where('slug', $slug)
                ->with([
                    'games' => function ($query) use ($gameSlug) {
                        $query->whereHas('game', function ($q) use ($gameSlug) {
                            $q->where('slug', $gameSlug);
                        });
                        $query->with(
                            'game',
                            'game.dates',
                            'game.dates.gamePlatform',
                            'game.titleImage',
                            'game.cover',
                            'game.genres',
                            'platform',
                            'addedBy',
                            'boardGame',
                        );
                    }
                ])
                ->first();

            return BgGameResource::make($boardGame->games->first());
        });
    }

    /**
     * @param Request $request
     * @param $slug
     * @param $gameSlug
     * @param BoardGame $BoardGame
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getActionsWithGameInEventByGameSlug(
        Request $request,
        $slug,
        $gameSlug,
        BoardGame $BoardGame
    )
    {
        if (!$slug) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        if (!$gameSlug) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerGameCacheService::LIST_PREFIX . '_' . $slug . '_' . $gameSlug;

        if (!$request->fullList) {
            $cacheKey .= '_' . $request->page . '_' . $request->perPage;
        }

        $cacheKey .= '_in_event';

        return Cache::remember($cacheKey, BgPlayerGameCacheService::TIME, function () use ($request, $BoardGame, $slug, $gameSlug) {
            $boardGame = $BoardGame::query()
                ->where('slug', $slug)
                ->with([
                    'games' => function ($query) use ($gameSlug) {
                        $query->whereHas('game', function ($q) use ($gameSlug) {
                            $q->where('slug', $gameSlug);
                        });
                    }
                ])
                ->first();

            if (!$boardGame->games->first()) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

            $playerGame = PlayerGame::query()
                ->where('board_game_game_list_id', $boardGame->games->first()->id)
                ->where('board_game_id', $boardGame->id)
                ->where('status', '!=',PlayerGame::CURRENT)
                ->with([
                    'user',
                    'user.avatar',
                    'game',
                    'game.game',
                    'game.game.titleImage',
                    'game.game.dates',
                    'game.game.dates.gamePlatform',
                    'game.game.cover',
                    'game.game.genres',
                    'game.platform',
                    'game.addedBy',
                    'comment',
                ]);

            $result = $request->fullList ? $playerGame->get() : $playerGame->paginate($request->perPage ? $request->perPage : 10);

            $resourceCollection = BgPlayerGameShortResource::collection($result);
            $standardResponse = $resourceCollection->response()->getData(true);

            $playerGamesList = PlayerGame::query()
                ->where('board_game_game_list_id', $boardGame->games->first()->id)
                ->where('board_game_id', $boardGame->id)
                ->where('status', '!=', PlayerGame::CURRENT)
                ->select(['id', 'status'])
                ->orderByDesc('id')
                ->get();

            $finalResponse = array_merge($standardResponse, [
                'data_for_chart' => [
                    $playerGamesList->where('status', PlayerGame::COMPLETED)->count(),
                    $playerGamesList->where('status', PlayerGame::REROLLED)->count(),
                    $playerGamesList->where('status', PlayerGame::GIVEN_AWAY)->count(),
                ],
            ]);

            return response()->json($finalResponse);
        });
    }

    /**
     * @param Request $request
     * @param $slug
     * @param $gameSlug
     * @param BoardGame $BoardGame
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getActionsWithGameInOtherEventsByGameSlug(
        Request $request,
        $slug,
        $gameSlug,
        BoardGame $BoardGame
    )
    {
        if (!$slug) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        if (!$gameSlug) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerGameCacheService::LIST_PREFIX . '_' . $slug . '_' . $gameSlug;

        if (!$request->fullList) {
            $cacheKey .= '_' . $request->page . '_' . $request->perPage;
        }

        $cacheKey .= '_in_other_events';

        return Cache::remember($cacheKey, BgPlayerGameCacheService::TIME, function () use ($request, $BoardGame, $slug, $gameSlug) {
            $boardGame = $BoardGame::query()
                ->where('slug', $slug)
                ->with([
                    'games' => function ($query) use ($gameSlug) {
                        $query->whereHas('game', function ($q) use ($gameSlug) {
                            $q->where('slug', $gameSlug);
                        });
                        $query->with(
                            'game',
                        );
                    }
                ])
                ->first();

            if (!$boardGame->games->first()) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

            $gameListIds = BoardGameGameList::query()
                ->where('game_id', $boardGame->games->first()->game->id)
                ->whereIn('board_game_id', function ($query) use ($boardGame) {
                    $query->select('id')
                        ->from('board_games')
                        ->where('is_test', '!=', true)
                        ->where('id', '!=', $boardGame->id);
                })
                ->pluck('id')
                ->toArray();

            $playerGame = PlayerGame::query()
                ->whereIn('board_game_game_list_id', $gameListIds)
                ->where('status', '!=',PlayerGame::CURRENT)
                ->with([
                    'user',
                    'user.avatar',
                    'game',
                    'game.game',
                    'game.game.titleImage',
                    'game.game.dates',
                    'game.game.dates.gamePlatform',
                    'game.game.cover',
                    'game.game.genres',
                    'game.platform',
                    'game.addedBy',
                    'comment',
                    'boardGame',
                ]);

            $result = $request->fullList ? $playerGame->get() : $playerGame->paginate($request->perPage ? $request->perPage : 10);

            $resourceCollection = BgPlayerGameShortResource::collection($result);
            $standardResponse = $resourceCollection->response()->getData(true);

            $playerGamesList = PlayerGame::query()
                ->whereIn('board_game_game_list_id', $gameListIds)
                ->where('status', '!=',PlayerGame::CURRENT)
                ->select(['id', 'status'])
                ->orderByDesc('id')
                ->get();

            $finalResponse = array_merge($standardResponse, [
                'data_for_chart' => [
                    $playerGamesList->where('status', PlayerGame::COMPLETED)->count(),
                    $playerGamesList->where('status', PlayerGame::REROLLED)->count(),
                    $playerGamesList->where('status', PlayerGame::GIVEN_AWAY)->count(),
                ],
            ]);

            return response()->json($finalResponse);
        });
    }
}
