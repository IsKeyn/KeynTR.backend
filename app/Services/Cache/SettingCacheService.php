<?php

namespace App\Services\Cache;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingCacheService extends BaseCacheService
{
    public const NAME = Setting::CACHE_NAME;
    public const MODEL = Setting::class;

    public const ADMIN_LIST_PREFIX = 'admin_' . self::NAME . '_list_cache';
    public const ADMIN_FILTER_PREFIX = 'admin_' . self::NAME . '_filter_cache';
    public const ADMIN_DETAIL_PREFIX = 'admin_' . self::NAME . '_detail_cache';
    public const ADMIN_ADDDATA_PREFIX = 'admin_' . self::NAME . '_adddata_cache';

    public const LIST_PREFIX = self::NAME . '_list_cache';
    public const FILTER_PREFIX =  self::NAME . '_filter_cache';
    public const DETAIL_PREFIX = self::NAME . '_detail_cache';

    public const LIST_TOKEN = self::NAME . '_list_token';
    public const LIST_FILTER_TOKEN = self::NAME . '_list_filter_token';

    public const ADMIN_LIST_TOKEN = self::NAME . '_list_token';

    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllCache()
    {
        self::clearListCache();
        self::clearAllDetailCache();
    }

    public function clearListCache(
        $showMessage = false,
        $siteId = 1,
        $entityType = null,
        $entityId = null
    )
    {
        $cacheKey = SettingCacheService::LIST_PREFIX . '_' . $siteId;

        if ($entityType) $cacheKey .= '_' . $entityType;
        if ($entityId) $cacheKey .= '_' . $entityId;

        Cache::forget($cacheKey);

        if ($showMessage) {
            echo $cacheKey . ' очищен' . PHP_EOL;
        }

        Cache::forget(self::LIST_TOKEN);
        Cache::forget(self::LIST_FILTER_TOKEN);
        Cache::forget(self::ADMIN_LIST_TOKEN);
    }

    public function clearAllDetailCache()
    {
        $data = self::MODEL::query()->get();

        foreach ($data as $element) {
            self::clearDetailCacheBySlug($element->slug);
            self::clearAdminDetailCacheById($element->id);
        }
    }

    public function clearDetailCacheBySlug($slug, $showMessage = false)
    {
        $cacheKey = self::DETAIL_PREFIX . '_' . $slug;

        Cache::forget($cacheKey);

        if ($showMessage) {
            echo $cacheKey . PHP_EOL;
        }
    }

    public function clearAdminDetailCacheById($id, $showMessage = false)
    {
        $cacheKey = self::ADMIN_DETAIL_PREFIX . '_' . $id;

        Cache::forget($cacheKey);

        if ($showMessage) {
            echo $cacheKey . PHP_EOL;
        }
    }

    public function clearAdminAddDataCache()
    {
        Cache::forget(SeriesCacheService::ADMIN_ADDDATA_PREFIX);
    }
}
