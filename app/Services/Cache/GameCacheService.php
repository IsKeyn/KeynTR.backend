<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use App\Models\Game;

class GameCacheService
{
    public const ADMIN_LIST_PREFIX = 'admin_game_list_cache';
    public const ADMIN_FILTER_PREFIX = 'admin_game_filter_detail_cache';
    public const ADMIN_DETAIL_PREFIX = 'admin_game_detail_cache';
    public const ADMIN_ADDDATA_PREFIX = 'admin_game_adddata_cache';

    public const LIST_PREFIX = 'game_list_cache';
    public const ROLL_LIST_PREFIX = 'game_roll_list_cache';
    public const FILTER_PREFIX = 'game_filter_cache';
    public const DETAIL_PREFIX = 'game_detail_cache';

    public const LIST_TOKEN = 'game_list_token';
    public const ROLL_LIST_TOKEN = 'game_roll_list_token';
    public const LIST_FILTER_TOKEN = 'game_list_filter_token';

    public const ADMIN_LIST_TOKEN = 'game_list_token';

    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllGameCache()
    {
        self::clearGameListCache();
        self::clearAllDetailCache();
    }

    public function clearGameListCache($showMessage = false)
    {
        $perPageArray = [24, 28, 96];

        foreach ($perPageArray as $perPage) {
            $lastPage = Game::query()
                ->where('show_in_list', true)
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

        Cache::forget(self::ROLL_LIST_PREFIX);
        Cache::forget(self::ROLL_LIST_TOKEN);
    }

    public function clearAllDetailCache()
    {
        $data = Game::query()->get();

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
        Cache::forget(GameCacheService::ADMIN_ADDDATA_PREFIX);
    }
}
