<?php

namespace App\Services\Cache\BoardGame;

use App\Models\BoardGame\BoardCellReview;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class BoardCellReviewCacheService extends BaseCacheService
{
    public const NAME = BoardCellReview::CACHE_NAME;
    public const MODEL = BoardCellReview::class;

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

    public function clearCellReviewsList(BoardCellReview $element): void
    {
        $this->clearCellReviewsInEventCache($element);
        $this->clearCellReviewsInOtherEventsCache($element);
    }

    public function clearCellReviewsInEventCache(BoardCellReview $element, bool $showMessage = false): void
    {
        $modelClass = static::MODEL;
        $origCacheKey = self::LIST_PREFIX . '_' . $element->board_game_id . '_' . $element->board_position_effects_id;

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->where('board_game_id', $element->board_game_id)
                ->where('board_position_effects_id', $element->board_position_effects_id)
                ->paginate($perPage)
                ->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = $origCacheKey . '_' . $i . '_' . $perPage . '_in_event';

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget($origCacheKey);
    }

    public function clearCellReviewsInOtherEventsCache(BoardCellReview $element, bool $showMessage = false): void
    {
        $bgId = $element->board_game_id;

        $modelClass = static::MODEL;
        $origCacheKey = self::LIST_PREFIX . '_' . $bgId . '_' . $element->board_position_effects_id;

        foreach (static::ARR_PER_PAGE as $perPage) {
            $lastPage = $modelClass::query()
                ->whereIn('board_game_id', function ($query) use ($bgId) {
                    $query->select('id')
                        ->from('board_games')
                        ->where('is_test', '!=', true)
                        ->where('id', '!=', $bgId);
                })
                ->where('board_position_effects_id', $element->board_position_effects_id)
                ->paginate($perPage)
                ->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = $origCacheKey . '_' . $i . '_' . $perPage . '_in_other_events';

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget($origCacheKey);
    }
}
