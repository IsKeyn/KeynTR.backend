<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecommendationRequest;
use App\Http\Resources\Admin\RecommendationResource;
use App\Models\Recommendation;
use App\Services\Cache\RecommendationCacheService;
use App\Services\MutationService;
use Illuminate\Support\Facades\Cache;

class AdminRecommendationController extends Controller {
    public function index()
    {
        $cacheKey = RecommendationCacheService::ADMIN_LIST_PREFIX;

        return Cache::remember($cacheKey, RecommendationCacheService::TIME, function () {
            return RecommendationResource::collection(Recommendation::with(['media'])->get());
        });
    }

    public function edit($id)
    {
        $cacheKey = RecommendationCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        return Cache::remember($cacheKey, RecommendationCacheService::TIME, function () use ($id) {
            $recommendation = Recommendation::findById($id)->with(['media'])->first();

            return RecommendationResource::make($recommendation);
        });
    }

    public function store(RecommendationRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        if (!$recommendation = Recommendation::create($validated)) return false;
        if (isset($validated['media_id'])) MutationService::setMedia($recommendation, $validated['media_id']);
        if (isset($validated['tags'])) MutationService::setTags($recommendation, $validated['tags']);

        $cacheService = app(RecommendationCacheService::class);
        $cacheService->clearListCache();

        return $recommendation;
    }

    public function update(RecommendationRequest $request, Recommendation $recommendation)
    {
        $validated = $request->validated();

        if (!$recommendation) return false;
        if (isset($validated['media_id'])) MutationService::setMedia($recommendation, $validated['media_id']);
        if (isset($validated['tags'])) MutationService::setTags($recommendation, $validated['tags']);

        $cacheService = app(RecommendationCacheService::class);
        $cacheService->clearListCache();
        $cacheService->clearDetailCacheById($recommendation->id);

        return $recommendation->update($validated);
    }
}
