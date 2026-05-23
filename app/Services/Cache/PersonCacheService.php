<?php

namespace App\Services\Cache;

use App\Models\Person\Person;
use Illuminate\Support\Facades\Cache;

class PersonCacheService
{
    public const ADMIN_LIST_PREFIX = 'admin_person_list_cache';
    public const ADMIN_FILTER_PREFIX = 'admin_person_filter_detail_cache';
    public const ADMIN_DETAIL_PREFIX = 'admin_person_detail_cache';
    public const ADMIN_ADDDATA_PREFIX = 'admin_person_adddata_cache';

    public const LIST_PREFIX = 'person_list_cache';
    public const FILTER_PREFIX = 'person_filter_cache';
    public const DETAIL_PREFIX = 'person_detail_cache';

    public const LIST_TOKEN = 'person_list_token';
    public const LIST_FILTER_TOKEN = 'person_list_filter_token';

    public const ADMIN_LIST_TOKEN = 'person_list_token';

    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllCache()
    {
        self::clearListCache();
        self::clearAllDetailCache();
    }

    public function clearListCache($showMessage = false)
    {
        $perPageArray = [24, 28, 96];

        foreach ($perPageArray as $perPage) {
            $lastPage = Person::query()
                ->active()
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = self::LIST_PREFIX . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget(self::LIST_TOKEN);
        Cache::forget(self::LIST_FILTER_TOKEN);
        Cache::forget(self::ADMIN_LIST_TOKEN);
    }

    public function clearAllDetailCache()
    {
        $data = Person::query()->get();

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
        Cache::forget(PersonCacheService::ADMIN_ADDDATA_PREFIX);
    }
}
