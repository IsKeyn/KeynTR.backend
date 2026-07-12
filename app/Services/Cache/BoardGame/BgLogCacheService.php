<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\BoardGameLog;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BgLogCacheService extends BaseCacheService
{
    public const NAME = BoardGameLog::CACHE_NAME;
    public const MODEL = BoardGameLog::class;

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
    public const ARR_PER_PAGE = [10, 20, 30];

    public function clearClientPlayerListCache($element)
    {
        $cacheKey = static::LIST_PREFIX . '_' . $element->boardGame->slug . '_' . $element->created_by;
        Cache::forget($cacheKey);

        $modelClass = static::MODEL;

        // Очистка списочного публичного кеша
        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->where('board_game_id', $element->boardGame->id)
                ->where('created_by', $element->created_by)
                ->orderByDesc('created_at')
                ->paginate($perPage)
                ->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = static::LIST_PREFIX . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);
            }
        }
    }
}
