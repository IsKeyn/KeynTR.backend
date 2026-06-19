<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\PlayerGame;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BgPlayerGameCacheService extends BaseCacheService
{
    public const NAME = PlayerGame::CACHE_NAME;
    public const MODEL = PlayerGame::class;

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

    public function clearClientDetailCache($element)
    {
        $currentGameCacheKey = BgPlayerGameCacheService::DETAIL_PREFIX . '_current_' . $element->boardGame->slug . '_' . $element->user->id;
        Cache::forget($currentGameCacheKey);
    }

    public function clearAllGameHistoryCache($boardGame, $showMessage = false)
    {
        $players = $boardGame->players;

        foreach ($players as $player) {
            $this->clearPlayerGameHistoryCache($boardGame, $player);
        }
    }

    public function clearPlayerGameHistoryCache($player, $showMessage = false)
    {
        $modelClass = static::MODEL;
        $origCacheKey = BgPlayerGameCacheService::LIST_PREFIX . '_' . $player->boardGame->slug . '_' . $player->user_id;

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->where('board_game_id', $player->boardGame->id)
                ->where('user_id', $player->user_id)
                ->paginate($perPage)
                ->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = $origCacheKey . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget($origCacheKey);
    }
}
