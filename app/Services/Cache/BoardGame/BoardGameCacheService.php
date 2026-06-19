<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BoardGameCacheService extends BaseCacheService
{
    public const NAME = BoardGame::CACHE_NAME;
    public const MODEL = BoardGame::class;

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

    public function clearClientPlayerListByBgCache($boardGame)
    {
        foreach ($boardGame->players as $player) {
            $this->clearClientPlayerListCacheFn($boardGame->slug, $player->user_id);
        }
    }

    public function clearClientPlayerListCache($player)
    {
        $this->clearClientPlayerListCacheFn($player->boardGame->slug, $player->user_id);
    }

    public function clearClientPlayerListCacheFn($slug, $userID)
    {
        $cacheKey = static::LIST_PREFIX . '_' . $slug . '_' . $userID;
        Cache::forget($cacheKey);
    }
}
