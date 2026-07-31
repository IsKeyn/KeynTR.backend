<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\PlayerInteractions;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BgPlayerInteractionCacheService extends BaseCacheService
{
    public const NAME = PlayerInteractions::CACHE_NAME;
    public const MODEL = PlayerInteractions::class;

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

    public function clearClientPlayerListCache($element)
    {
        $cacheKey1 = BgPlayerInteractionCacheService::LIST_PREFIX . '_' . $element->boardGame->slug . '_' . $element->created_by . '_true';
        $cacheKey2 = BgPlayerInteractionCacheService::LIST_PREFIX . '_' . $element->boardGame->slug . '_' . $element->created_by . '_false';
        $cacheKey3 = BgPlayerInteractionCacheService::LIST_PREFIX . '_' . $element->boardGame->slug . '_' . $element->with_player . '_true';
        $cacheKey4 = BgPlayerInteractionCacheService::LIST_PREFIX . '_' . $element->boardGame->slug . '_' . $element->with_player . '_false';

        Cache::forget($cacheKey1);
        Cache::forget($cacheKey2);
        Cache::forget($cacheKey3);
        Cache::forget($cacheKey4);
    }
}
