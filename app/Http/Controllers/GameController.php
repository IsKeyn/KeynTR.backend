<?php

namespace App\Http\Controllers;

use App\Http\Resources\Game\GameDetailResource;
use App\Http\Resources\Game\GameListResource;
use App\Http\Resources\UserActions\UserActionsResource;
use App\Models\Game;
use App\Services\Cache\GameCacheService;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function getList(Request $request) {
        $cacheKey = GameCacheService::LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;

        return Cache::remember($cacheKey, GameCacheService::TIME, function () use ($request) {
            $games = Game::query()
                ->with(['media', 'genres', 'dates'])
                ->where('show_in_list', true)
                ->active()
                ->paginate($request->perPage ? $request->perPage : 10);

            return GameListResource::collection($games);
        });
    }

    public function getGame(Request $request, $slug) {
        $game = Game::findBySlug($slug)->active()
            ->with([
                'views',
                'likes',
                'comments',
            ])
            ->first();

        if ($game) {
            ViewsLogService::set($request, get_class($game), $game->id);

            $cacheKey = GameCacheService::DETAIL_PREFIX . '_' . $slug;

            $data = Cache::remember($cacheKey, GameCacheService::TIME, function () use ($request, $slug) {
                $game = Game::findBySlug($slug)->active()
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
                    ])
                    ->first();

                return GameDetailResource::make($game);
            });

            return [
                ...$data->toArray(request()),
                ...UserActionsResource::make($game)->toArray(request())
            ];
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }
}
