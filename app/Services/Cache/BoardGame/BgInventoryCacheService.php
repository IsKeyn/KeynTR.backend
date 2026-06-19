<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BgInventoryCacheService extends BaseCacheService
{
    public const NAME = BoardGameInventory::CACHE_NAME;
    public const MODEL = BoardGameInventory::class;

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

    public function clearDetailCacheAllTypes($element)
    {
        $this->clearClientDetailCache($element);
        $this->clearDetailCacheBySlug($element->slug);
        $this->clearAdminDetailCacheById($element->id);
    }

    public function clearClientPlayerListCache($player)
    {
        $cacheKey = static::LIST_PREFIX . '_' . $player->boardGame->slug . '_' . $player->user_id;
        Cache::forget($cacheKey);
    }
}
