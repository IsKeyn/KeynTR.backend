<?php

namespace App\Services\Cache;

use App\Models\Version;
use Illuminate\Support\Facades\Cache;

class VersionCacheService
{
    public const LIST_PREFIX = 'version_list_cache';
    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllCache()
    {

    }

    public function clearListCache($showMessage = false)
    {
        $perPageArray = [15, 30, 45];

        foreach ($perPageArray as $perPage) {
            $lastPage = Version::query()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = self::LIST_PREFIX . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget("version_token");
    }

    public function clearListCacheByEntity($type, $id, $showMessage = false)
    {
        $perPageArray = [5, 15, 30];

        foreach ($perPageArray as $perPage) {
            $lastPage = Version::query()
                ->where('entity_type', $type)
                ->where('entity_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = self::LIST_PREFIX . '_' . $type . '_' . $id . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget("version_token:{$type}:{$id}");
    }
}
