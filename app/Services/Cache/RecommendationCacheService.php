<?php

namespace App\Services\Cache;

use App\Models\Recommendation;
use Illuminate\Support\Facades\Cache;

class RecommendationCacheService extends BaseCacheService
{
    public const NAME = Recommendation::CACHE_NAME;
    public const MODEL = Recommendation::class;

    public const LIST_PREFIX = 'recommendation_list_cache';
    public const DETAIL_PREFIX = 'recommendation_detail_cache';
    public const ADMIN_LIST_PREFIX = 'admin_recommendation_list_cache';
    public const ADMIN_DETAIL_PREFIX = 'admin_recommendation_detail_cache';
    public const TIME = 6 * 30 * 24 * 60 * 60;

    public function clearAllGameCache()
    {
        self::clearListCache();
        self::clearAllDetailCache();
    }

    public function clearListCache($showMessage = false)
    {
        Cache::forget(self::LIST_PREFIX);
        Cache::forget(self::ADMIN_LIST_PREFIX);
    }

    public function clearAllDetailCache()
    {
        $data = Recommendation::query()->get();

        foreach ($data as $element) {
            self::clearDetailCacheById($element->id);
        }
    }

    public function clearDetailCacheById($id)
    {
        Cache::forget(self::DETAIL_PREFIX . '_' . $id);
        Cache::forget(self::ADMIN_DETAIL_PREFIX . '_' . $id);
    }
}
