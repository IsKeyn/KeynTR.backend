<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\BoardGameGameList;
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

    public const ARR_PER_PAGE = [10, 20, 40];

    public function clearClientDetailCache($element)
    {
        if (!$element->boardGame || !$element->game) {
            return;
        }

        $detailGameCacheKey = BgPlayerGameCacheService::DETAIL_PREFIX . '_' . $element->boardGame->slug . '_' . $element->game->slug;
        Cache::forget($detailGameCacheKey);

        if (!$element->user) {
            return;
        }

        $currentGameCacheKey = BgPlayerGameCacheService::DETAIL_PREFIX . '_current_' . $element->boardGame->slug . '_' . $element->user->id;
        Cache::forget($currentGameCacheKey);
    }

    public function clearAllGameHistoryCache($boardGame, $showMessage = false)
    {
        $players = $boardGame->players;

        foreach ($players as $player) {
            $this->clearPlayerGameHistoryCache($player);
        }
    }

    public function clearActionsWithGameList($element)
    {
        $this->clearActionsWithGameInEventByGameSlugCache($element);
        $this->clearActionsWithGameInOtherEventsByGameCache($element);
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

    public function clearActionsWithGameInEventByGameSlugCache($playerGame, $showMessage = false)
    {
        $modelClass = static::MODEL;
        $origCacheKey = BgPlayerGameCacheService::LIST_PREFIX . '_' . $playerGame->boardGame->slug . '_' . $playerGame->game->slug . '_in_event';

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->where('board_game_game_list_id', $playerGame->id)
                ->where('board_game_id', $playerGame->boardGame->id)
                ->where('status', '!=', PlayerGame::CURRENT)
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

    public function clearActionsWithGameInOtherEventsByGameCache($playerGame, $showMessage = false)
    {
        $modelClass = static::MODEL;
        $origCacheKey = BgPlayerGameCacheService::LIST_PREFIX . '_' . $playerGame->boardGame->slug . '_' . $playerGame->game->slug . '_in_other_events';

        $gameListIds = BoardGameGameList::query()
            ->where('game_id', $playerGame->game->id)
            ->whereIn('board_game_id', function ($query) use ($playerGame) {
                $query->select('id')
                    ->from('board_games')
                    ->where('is_test', '!=', true)
                    ->where('id', '!=', $playerGame->boardGame->id);
            })
            ->pluck('id')
            ->toArray();

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->whereIn('board_game_game_list_id', $gameListIds)
                ->where('status', '!=',PlayerGame::CURRENT)
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
