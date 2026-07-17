<?php
namespace App\Http\Controllers\Admin;

use App\Filters\GameFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\Admin\AdminGameListResource;
use App\Http\Resources\Character\ShortResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GamingPlatformResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Person\PersonShortResource;
use App\Http\Resources\Series\SeriesResource;
use App\Models\Character;
use App\Models\Company;
use App\Models\Game;
use App\Models\Person\Person;
use App\Models\Series;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Version;
use App\Services\Cache\GameCacheService;
use App\Services\GameService;
use App\Services\RelatedDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminGameController extends Controller {
    public function index(Request $request)
    {
        $cacheKey = GameCacheService::ADMIN_LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;
        $time = GameCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                GameCacheService::ADMIN_LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = GameCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request) {
            $filter = new GameFilter($request);
            $games = $filter->apply(Game::query())->with(['titleImage']);

            if (!isset($request->sort)) {
                $games->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $games->paginate($request->perPage ? $request->perPage : 10);

            return AdminGameListResource::collection($result);
        });
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

            return $game;
        }
    }

    public function update(GameRequest $request, Game $game)
    {
        $validated = $request->validated();

        $game->fill($validated);

        /*
         * Код отвечает за то, чтобы Observer updated сработал только 1 раз, не зависимо от того, было обновления
         * основной таблицы или же обновились связи
         */
        $attributesChanged = $game->isDirty();

        if ($attributesChanged) {
            $game->save();
        }

        $relatedDataService = app(RelatedDataService::class);
        $relatedDataService->set($game, $validated);

        if (!$attributesChanged) {
            $game->touch();
        }

        return true;
    }

    public function edit(Request $request, $id)
    {
        if ($request->version_id) {
            return Version::findById($request->version_id)->first();
        } else {
            return GameService::getGameById($id);
        }
    }

    public function destroy(Game $game)
    {
        return $game->delete();
    }

    public function forceDelete($id)
    {
        $game = Game::findById($id)->withTrashed()->first();
        if (!$game) return false;

        return $game->forceDelete();
    }

    public function recovery($id)
    {
        $game = Game::findById($id)->withTrashed()->first();
        if (!$game) return false;

        return $game->restore();
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
                'person_role' => GroupResource::collection(Group::where('entity_type', 'App\Models\Person\Person')->get()),
                'character_role' => GroupResource::collection(Group::where('entity_type', 'App\Models\Character')->get()),
                'series' => SeriesResource::collection(Series::all()),
                'people' => PersonShortResource::collection(Person::all()),
                'character' => ShortResource::collection(Character::all()),
            ];
        });
    }
}
