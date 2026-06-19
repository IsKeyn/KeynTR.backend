<?php

namespace App\Services\Cache\BoardGame\StatusEffect;

use App\Models\BoardGame\PlayerStatusEffect;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BgPlayerStatusEffectCacheService extends BaseCacheService
{
    public const NAME = PlayerStatusEffect::CACHE_NAME;
    public const MODEL = PlayerStatusEffect::class;

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
        $cacheKey = static::LIST_PREFIX . '_' . $element->boardGame->slug . '_' . $element->user_id;
        Cache::forget($cacheKey);
    }
}
