<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

abstract class BaseCacheService
{
    /* НАЧАЛО: ОБЯЗАТЕЛЬНО переопределите эти константы в родительском классе */
    public const NAME = '';
    public const MODEL = '';

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
    /* КОНЕЦ: ОБЯЗАТЕЛЬНО переопределите эти константы в родительском классе */

    public const ARR_PER_PAGE = [24, 28, 96];
    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllCache()
    {
        $this->clearListCache();
        $this->clearAllDetailCache();
    }

    public function clearListCache($showMessage = false)
    {
        $modelClass = static::MODEL;

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->active()
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = static::LIST_PREFIX . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
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
