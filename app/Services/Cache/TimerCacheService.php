<?php

namespace App\Services\Cache;

use App\Models\BoardGame\Timer;
use Illuminate\Support\Facades\Cache;

class TimerCacheService extends BaseCacheService
{
    public const NAME = Timer::CACHE_NAME;
    public const MODEL = Timer::class;

    public const ADMIN_LIST_PREFIX = 'admin_' . self::NAME . '_list_cache';
    public const ADMIN_FILTER_PREFIX = 'admin_' . self::NAME . '_filter_cache';
    public const ADMIN_DETAIL_PREFIX = 'admin_' . self::NAME . '_detail_cache';
    public const ADMIN_ADDDATA_PREFIX = 'admin_' . self::NAME . '_adddata_cache';

    public const LIST_PREFIX = self::NAME . '_list_cache';
    public const FILTER_PREFIX = self::NAME . '_filter_cache';
    public const DETAIL_PREFIX = self::NAME . '_detail_cache';

    public const LIST_TOKEN = self::NAME . '_list_token';
    public const LIST_FILTER_TOKEN = self::NAME . '_list_filter_token';
    public const ADMIN_LIST_TOKEN = self::NAME . '_list_token';

    public const ARR_PER_PAGE = [24, 28, 96];
    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllCache()
    {
        $this->clearListCache();
        $this->clearAllDetailCache();
    }

    /**
     * Сбрасывает кэш списков
     * @param array $params набор параметров
     * @return void
     */
    public function clearListCache($showMessage = false, $params = []) : void
    {
        if (isset($params['userId'])) {
            Cache::forget(static::LIST_PREFIX . '_' . $params['userId']);
        }

        Cache::forget(static::LIST_PREFIX);
        Cache::forget(static::LIST_TOKEN);
        Cache::forget(static::LIST_FILTER_TOKEN);
        Cache::forget(static::ADMIN_LIST_TOKEN);
    }

    public function clearAllDetailCache()
    {
        $data = static::MODEL::query()->get();

        foreach ($data as $element) {
            $this->clearDetailCacheBySlug($element->slug);
            $this->clearAdminDetailCacheById($element->id);
        }
    }

    public function clearDetailCacheBySlug($slug, $showMessage = false)
    {
        $cacheKey = static::DETAIL_PREFIX . '_' . $slug;
        Cache::forget($cacheKey);

        if ($showMessage) echo $cacheKey . PHP_EOL;
    }

    public function clearAdminDetailCacheById($id, $showMessage = false)
    {
        $cacheKey = static::ADMIN_DETAIL_PREFIX . '_' . $id;
        Cache::forget($cacheKey);

        if ($showMessage) echo $cacheKey . PHP_EOL;
    }
}
