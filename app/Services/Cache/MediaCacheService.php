<?php

namespace App\Services\Cache;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;

class MediaCacheService
{
    public const LIST_PREFIX = 'media_list_cache';
    public const DETAIL_PREFIX = 'media_detail_cache';
    public const TIME = 6 * 30 * 24 * 60 * 60;

    public function clearAllCache()
    {
        self::clearAllDetailCache();
    }

    public function clearAllDetailCache()
    {
        $allMedia = Media::query()->get();

        foreach ($allMedia as $media) {
            self::clearDetailCacheById($media->id);
        }
    }

    public function clearDetailCacheById($id)
    {
        $cacheKey = self::DETAIL_PREFIX . '_' . $id;

        Cache::forget($cacheKey);

        echo $cacheKey . PHP_EOL;
    }
}
