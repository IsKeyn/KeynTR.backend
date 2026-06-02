<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\BoardGamePlayer;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BgPlayerCacheService extends BaseCacheService
{
    public const NAME = BoardGamePlayer::CACHE_NAME;
    public const MODEL = BoardGamePlayer::class;

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

    public function clearAllDetailCache()
    {
        $data = static::MODEL::query()->get();

        foreach ($data as $element) {
            $this->clearDetailCacheAllTypes($element);
        }
    }

    public function clearDetailCacheAllTypes($element)
    {
        $this->clearClientDetailCache($element);
        $this->clearDetailCacheBySlug($element->slug);
        $this->clearAdminDetailCacheById($element->id);
    }

    public function clearClientDetailCache($element)
    {
        $cacheKey = static::DETAIL_PREFIX . '_' . $element->boardGame->slug . '_' . $element->user->id;
        Cache::forget($cacheKey);
    }
}
