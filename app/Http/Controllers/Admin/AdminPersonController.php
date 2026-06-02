<?php
namespace App\Http\Controllers\Admin;

use App\Filters\PersonFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\PersonRequest;
use App\Http\Resources\Admin\Person\AdminPersonListResource;
use App\Http\Resources\Game\GameShortestResource;
use App\Models\Game;
use App\Models\Person\Person;
use App\Models\Version;
use App\Services\Cache\PersonCacheService;
use App\Services\RelatedDataService;
use App\Services\PersonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminPersonController extends Controller {
    public function index(Request $request)
    {
        $cacheKey = PersonCacheService::ADMIN_LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;
        $time = PersonCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                PersonCacheService::ADMIN_LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = PersonCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request) {
            $filter = new PersonFilter($request);
            $elementsList = $filter->apply(Person::query())->with([]);

            if (!isset($request->sort)) {
                $elementsList->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $elementsList->paginate($request->perPage ? $request->perPage : 10);

            return AdminPersonListResource::collection($result);
        });
    }

    public function store(PersonRequest $request)
    {
        $validated = $request->validated();

        if (!isset($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        if (!$validated['created_at']) {
            unset($validated['created_at']);
        }

        if ($item = Person::create($validated)) {
            $relatedDataService = app(RelatedDataService::class);
            $relatedDataService->set($item, $validated);

            return $item;
        }
    }

    public function update(PersonRequest $request, Person $person)
    {
        $validated = $request->validated();

        $person->fill($validated);

        /*
         * Код отвечает за то, чтобы Observer updated сработал только 1 раз, не зависимо от того, было обновления
         * основной таблицы или же обновились связи
         */
        $attributesChanged = $person->isDirty();

        if ($attributesChanged) {
            $person->save();
        }

        $relatedDataService = app(RelatedDataService::class);
        $relatedDataService->set($person, $validated);

        if (!$attributesChanged) {
            $person->touch();
        }

        return true;
    }

    public function edit(Request $request, $id)
    {
        if ($request->version_id) {
            return Version::findById($request->version_id)->first();
        } else {
            return PersonService::getById($id);
        }
    }

    public function destroy(Person $person)
    {
        return $person->delete();
    }

    public function forceDelete($id)
    {
        $person = Person::findById($id)->withTrashed()->first();
        if (!$person) return false;

        return $person->forceDelete();
    }

    public function recovery($id)
    {
        $person = Person::findById($id)->withTrashed()->first();
        if (!$person) return false;

        return $person->restore();
    }

    public function getAdditionalData()
    {
        return Cache::remember(PersonCacheService::ADMIN_ADDDATA_PREFIX, PersonCacheService::TIME, function () {
            return [
                'game' => GameShortestResource::collection(Game::query()->with(['titleImage'])->orderByRaw('sort IS NULL, sort ASC')->get()),
            ];
        });
    }
}
