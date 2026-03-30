<?php

namespace App\Http\Controllers;

use App\Filters\GameFilter;
use App\Http\Resources\BoardGame\BoardGameShortestWithImageResource;
use App\Http\Resources\Game\GameDetailResource;
use App\Http\Resources\Game\GameListResource;
use App\Http\Resources\UserActions\UserActionsResource;
use App\Models\Game;
use App\Services\Cache\GameCacheService;
use App\Services\Game\FilterService;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function getList(Request $request) {
        $cacheKey = GameCacheService::LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;

        if ($request->filters) $cacheKey .= '_' . $request->filters;

        return Cache::remember($cacheKey, GameCacheService::TIME, function () use ($request) {
            $filter = new GameFilter($request);
            $games = $filter->apply(Game::query())
                ->with(['media', 'genres', 'dates'])
                ->where('show_in_list', true)
                ->active();

            if (!isset($request->sort)) {
                $games->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $games->paginate($request->perPage ? $request->perPage : 10);

            return GameListResource::collection($result);
        });
    }

    public function getListFilters(Request $request, FilterService $filterService) {
        $cacheKey = GameCacheService::FILTER_PREFIX . '_' . $request->filterList;

        if ($request->filters) $cacheKey .= '_' . $request->filters;

        return Cache::remember($cacheKey, GameCacheService::TIME, function () use ($request, $filterService) {
            if ($request->filterList) {
                $filterList = json_decode($request->filterList);

                // Получаем список всех игр
                $games = Game::query()
                    ->with(['genres', 'company', 'dates', 'tags', 'gamePlatform'])
                    ->where('show_in_list', true)
                    ->active()
                    ->get();

                $result = $filterService->get($games, $filterList);

                if (
                    $request->filters
                    && $decodedFilters = json_decode($request->filters)
                    && isset($decodedFilters['disableUnusedFilters'])
                    && $decodedFilters['disableUnusedFilters'] === true
                ) {
                    // Получаем отфильтрованный список игр
                    $filter = new GameFilter($request);
                    $filteredGames = $filter->apply(Game::query())
                        ->with(['genres', 'company', 'dates', 'tags', 'gamePlatform'])
                        ->where('show_in_list', true)
                        ->active()
                        ->get();

                    $availableFilters = $filterService->get($filteredGames, $filterList);
                    $result = $filterService->compareFilters($result, $availableFilters);
                }

                return $result;
            }
        });
    }

    public function getGame(Request $request, $slug) {
        $game = Game::findBySlug($slug)
            ->with([
                'views',
                'likes',
                'comments',
            ]);

        if (!$request->preview) {
            $game->active();
        }

        $game = $game->first();

        if ($game) {
            if (!$request->preview) {
                ViewsLogService::set($request, get_class($game), $game->id);
            }

            $cacheKey = GameCacheService::DETAIL_PREFIX . '_' . $slug;

            $data = Cache::remember($cacheKey, GameCacheService::TIME, function () use ($request, $slug) {
                $game = Game::findBySlug($slug)
                    ->with([
                        'titleImage',
                        'cover',
                        'gamePlatform',
                        'dates',
                        'dates.gamePlatform',
                        'anonsDates',
                        'tags',
                        'groups',
                        'genres',
                        'company',
                        'company.group',
                        'link',
                        'additionalFields',
                        'seo',
                        'seo.entity',
                        'seo.entity.tags',
                        'menu',
                        'menu.elements',
                        'blocks',
                        'bgGamesList',
                        'bgGamesList.boardGame',
                        'bgGamesList.boardGame.titleImage',
                    ]);

                if (!$request->preview) {
                    $game->active();
                }

                $game = $game->first();

                $boardGames['boardGames'] = [];

                if ($game->relationLoaded('bgGamesList')) {
                    foreach ($game->bgGamesList->sortByDesc('id') as $gameGameList) {
                        if ($gameGameList->relationLoaded('boardGame') && $gameGameList->boardGame->slug !== 'demo') {
                            $boardGames['boardGames'][] = BoardGameShortestWithImageResource::make($gameGameList->boardGame);
                        }
                    }
                }

                return [
                    ...GameDetailResource::make($game)->toArray(request()),
                    ...$boardGames,
                ];
            });

            return [
                ...$data,
                ...UserActionsResource::make($game)->toArray(request()),
            ];
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }
}
