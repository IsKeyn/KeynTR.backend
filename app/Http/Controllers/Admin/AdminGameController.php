<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\Admin\AdminGameResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GamingPlatformResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Series\SeriesResource;
use App\Models\Company;
use App\Models\Game;
use App\Models\Series;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Version;
use App\Services\Cache\GameCacheService;
use App\Services\RelatedDataService;
use App\Services\VersionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminGameController extends Controller {
    public function index(Game $game)
    {
        return $game::all();
    }

    public function store(GameRequest $request)
    {
        $validated = $request->validated();

        if (!isset($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        if (!$validated['created_at']) {
            unset($validated['created_at']);
        }

        if ($game = Game::create($validated)) {
            $relatedDataService = app(RelatedDataService::class);
            $relatedDataService->set($game, $validated);

            $this->clearCache($game);

            return $game;
        }
    }

    public function update(GameRequest $request, Game $game)
    {
        $validated = $request->validated();

        $relatedDataService = app(RelatedDataService::class);
        $relatedDataService->set($game, $validated);

        $this->clearCache($game);

        $version = $this->getGameById($game->id)->toArray(request());

        VersionService::set($version, $game->model, $game->id);

        return $game->update($validated);
    }

    public function clearCache($game)
    {
        $gameCacheService = app(GameCacheService::class);
        $gameCacheService->clearGameListCache();
        $gameCacheService->clearAdminDetailCacheById($game->id);
        $gameCacheService->clearDetailCacheBySlug($game->slug);
    }

    public function edit(Request $request, $id)
    {
        if ($request->version_id) {
            return Version::findById($request->version_id)->first();
        } else {
            return $this->getGameById($id);
        }
    }

    private function getGameById($id)
    {
        $cacheKey = GameCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        return Cache::remember($cacheKey, GameCacheService::TIME, function () use ($id) {
            $game = Game::findById($id)
                ->with([
                    'titleImage',
                    'cover',
                    'gamePlatform',
                    'dates',
                    'dates.gamePlatform',
                    'anonsDates',
                    'tags',
                    'series',
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
                ])
                ->first();

            return AdminGameResource::make($game);
        });
    }

    public function getAdditionalData()
    {
        return Cache::remember(GameCacheService::ADMIN_ADDDATA_PREFIX, GameCacheService::TIME, function () {
            return [
                'group' => GroupResource::collection(
                    Group::where('entity_type', 'App\Models\Game')->where('type', Game::SERIES_TYPE)->get()
                ),
                'gaming_platform' => GamingPlatformResource::collection(GamingPlatform::all()),
                'genre' => GenreResource::collection(Genre::all()),
                'company' => CompanyResource::collection(Company::all()),
                'company_role' => GroupResource::collection(Group::where('entity_type', 'App\Models\Company')->get()),
                'series' => SeriesResource::collection(Series::all()),
            ];
        });
    }
}
