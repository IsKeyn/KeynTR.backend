<?php
namespace App\Http\Controllers\Admin;

use App\Filters\VersionFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminVersionResource;
use App\Models\Version;
use App\Services\Cache\VersionCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminVersionController extends Controller {
    public function index(Request $request)
    {
        $cacheKey = VersionCacheService::LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;
        $time = VersionCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                VersionCacheService::ADMIN_LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = VersionCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request) {
            $filter = new VersionFilter($request);

            $versions = $filter->apply(Version::query())
                ->select([
                    'id',
                    'name',
                    'entity_type',
                    'entity_id',
                    'do_type',
                    'sort',
                    'active',
                    'created_by',
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('id', 'desc');

            if (!isset($request->sort)) {
                $versions->orderBy('sort', 'asc');
            }

            $result = $versions->paginate($request->perPage ? $request->perPage : 10);

            return AdminVersionResource::collection($result);
        });
    }
//
//    public function edit($id)
//    {
//        $cacheKey = RecommendationCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;
//
//        return Cache::remember($cacheKey, RecommendationCacheService::TIME, function () use ($id) {
//            $recommendation = Recommendation::findById($id)->with(['media'])->first();
//
//            return RecommendationResource::make($recommendation);
//        });
//    }
//
//    public function store(RecommendationRequest $request)
//    {
//        $validated = $request->validated();
//        $validated['created_by'] = auth()->id();
//
//        if (!$recommendation = Recommendation::create($validated)) return false;
//        if (isset($validated['media_id'])) MutationService::setMedia($recommendation, $validated['media_id']);
//        if (isset($validated['tags'])) MutationService::setTags($recommendation, $validated['tags']);
//
//        $cacheService = app(RecommendationCacheService::class);
//        $cacheService->clearListCache();
//
//        return $recommendation;
//    }
//
//    public function update(RecommendationRequest $request, Recommendation $recommendation)
//    {
//        $validated = $request->validated();
//
//        if (!$recommendation) return false;
//        if (isset($validated['media_id'])) MutationService::setMedia($recommendation, $validated['media_id']);
//        if (isset($validated['tags'])) MutationService::setTags($recommendation, $validated['tags']);
//
//        $cacheService = app(RecommendationCacheService::class);
//        $cacheService->clearListCache();
//        $cacheService->clearDetailCacheById($recommendation->id);
//
//        return $recommendation->update($validated);
//    }

    public function getByEntity(Request $request)
    {
        if (!$request->entity_type || !$request->entity_id) {
            return response()->json(['message' => 'Missing parameters'], 400);
        }

        $cacheKey = VersionCacheService::LIST_PREFIX . '_' . $request->entity_type . '_' . $request->entity_id . '_' . $request->page . '_' . $request->perPage;
        $time = VersionCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                VersionCacheService::ADMIN_ELEMENT_TOKEN . ":{$request->entity_type}:{$request->entity_id}",
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = VersionCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request) {
            $filter = new VersionFilter($request);

            $versions = $filter->apply(Version::query())
                ->select([
                    'id',
                    'name',
                    'entity_type',
                    'entity_id',
                    'do_type',
                    'sort',
                    'active',
                    'created_by',
                    'created_at',
                    'updated_at'
                ])
                ->where('entity_type', $request->entity_type)
                ->where('entity_id', $request->entity_id)
                ->orderBy('id', 'desc');

            if (!isset($request->sort)) {
                $versions->orderBy('sort', 'asc');
            }

            $result = $versions->paginate($request->perPage ? $request->perPage : 10);

            return AdminVersionResource::collection($result);
        });
    }
}
