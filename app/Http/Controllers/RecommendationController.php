<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecommendationResource;
use App\Models\Recommendation;
use App\Services\Cache\RecommendationCacheService;
use Illuminate\Support\Facades\Cache;

class RecommendationController extends Controller
{
    public function get() {
        $cacheKey = RecommendationCacheService::LIST_PREFIX;

        return Cache::remember($cacheKey, RecommendationCacheService::TIME, function () {
            return RecommendationResource::collection(Recommendation::with(['media'])->orderBy('sort')->get());
        });
    }
}
