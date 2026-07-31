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

    public const ARR_PER_PAGE_ADMIN = [15, 30, 60];
    public const ARR_PER_PAGE = [15, 30, 45];

    public function clearBgListCache($boardGame, $showMessage = false)
    {
        $modelClass = static::MODEL;

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->where('board_game_id', $boardGame->id)
                ->active()
                ->paginate($perPage)
                ->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = static::LIST_PREFIX . '_' . $boardGame->slug . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget(static::LIST_PREFIX);
        Cache::forget(static::LIST_PREFIX . '_' . $boardGame->slug);
        Cache::forget(static::LIST_TOKEN);
        Cache::forget(static::LIST_FILTER_TOKEN);
        Cache::forget(static::ADMIN_LIST_TOKEN);
    }

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
        $cacheKey = static::DETAIL_PREFIX . '_' . $element->boardGame->slug . '_' . $element->user_id;
        Cache::forget($cacheKey);

        $layoutCacheKey = static::DETAIL_PREFIX . '_' . $element->boardGame->slug . '_' . $element->user_id . '_layout';
        Cache::forget($layoutCacheKey);

        $withInventoryCacheKey = static::DETAIL_PREFIX . '_' . $element->id . '_with_inventory';
        Cache::forget($withInventoryCacheKey);

        $withInventoryCacheKey = static::DETAIL_PREFIX . '_' . $element->id . '_with_inventory_obs';
        Cache::forget($withInventoryCacheKey);

        $boardCacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $element->boardGame->slug . '_' . $element->user_id . '_board';
        Cache::forget($boardCacheKey);
    }
}
