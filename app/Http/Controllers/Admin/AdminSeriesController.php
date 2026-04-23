<?php
namespace App\Http\Controllers\Admin;

use App\Filters\SeriesFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesRequest;
use App\Http\Resources\Admin\AdminSeriesListResource;
use App\Http\Resources\Game\GameShortestResource;
use App\Models\Game;
use App\Models\Series;
use App\Models\Version;
use App\Services\Cache\SeriesCacheService;
use App\Services\RelatedDataService;
use App\Services\SeriesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminSeriesController extends Controller {
    public function index(Request $request)
    {
        $cacheKey = SeriesCacheService::ADMIN_LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;
        $time = SeriesCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                SeriesCacheService::ADMIN_LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = SeriesCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request) {
            $filter = new SeriesFilter($request);
            $elementsList = $filter->apply(Series::query())->with([]);

            if (!isset($request->sort)) {
                $elementsList->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $elementsList->paginate($request->perPage ? $request->perPage : 10);

            return AdminSeriesListResource::collection($result);
        });
    }

    public function store(SeriesRequest $request)
    {
        $validated = $request->validated();

        if (!isset($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        if (!$validated['created_at']) {
            unset($validated['created_at']);
        }

        if ($item = Series::create($validated)) {
            $relatedDataService = app(RelatedDataService::class);
            $relatedDataService->set($item, $validated);

            return $item;
        }
    }

    public function update(SeriesRequest $request, Series $series)
    {
        $validated = $request->validated();

        $series->fill($validated);

        /*
         * Код отвечает за то, чтобы Observer updated сработал только 1 раз, не зависимо от того, было обновления
         * основной таблицы или же обновились связи
         */
        $attributesChanged = $series->isDirty();

        if ($attributesChanged) {
            $series->save();
        }

        $relatedDataService = app(RelatedDataService::class);
        $relatedDataService->set($series, $validated);

        if (!$attributesChanged) {
            $series->touch();
        }

        return true;
    }

    public function edit(Request $request, $id)
    {
        if ($request->version_id) {
            return Version::findById($request->version_id)->first();
        } else {
            return SeriesService::getById($id);
        }
    }

    public function destroy(Series $series)
    {
        return $series->delete();
    }

    public function forceDelete($id)
    {
        $series = Series::findById($id)->withTrashed()->first();
        if (!$series) return false;

        return $series->forceDelete();
    }

    public function recovery($id)
    {
        $series = Series::findById($id)->withTrashed()->first();
        if (!$series) return false;

        return $series->restore();
    }

    public function getAdditionalData()
    {
        return Cache::remember(SeriesCacheService::ADMIN_ADDDATA_PREFIX, SeriesCacheService::TIME, function () {
            return [
                'game' => GameShortestResource::collection(Game::query()->with(['titleImage'])->orderByRaw('sort IS NULL, sort ASC')->get()),
            ];
        });
    }
}
