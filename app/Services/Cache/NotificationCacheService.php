<?php

namespace App\Services\Cache;


use App\Models\User\Notification;
use Illuminate\Support\Facades\Cache;

class NotificationCacheService
{
    private const NAME = Notification::CACHE_NAME;
    private const MODEL = Notification::class;

    public const ADMIN_LIST_PREFIX = 'admin_' . self::NAME . '_list_cache';
    public const ADMIN_FILTER_PREFIX = 'admin_' . self::NAME . '_filter_detail_cache';
    public const ADMIN_DETAIL_PREFIX = 'admin_' . self::NAME . '_detail_cache';
    public const ADMIN_ADDDATA_PREFIX = 'admin_' . self::NAME . '_adddata_cache';

    public const ALL_NOTIFICATION_COUNT_PREFIX = self::NAME . '_all_notification_count_cache';

    public const LIST_PREFIX = self::NAME . '_list_cache';
    public const FILTER_PREFIX = 'filter_detail_cache';
    public const DETAIL_PREFIX = self::NAME . '_detail_cache';

    public const LIST_TOKEN = self::NAME . '_list_token';
    public const LIST_FILTER_TOKEN = self::NAME . '_list_filter_token';

    public const ADMIN_LIST_TOKEN = self::NAME . '_list_token';

    public const TIME = 6 * 30 * 24 * 60 * 60;
    public const FILTER_TIME = 15 * 24 * 60 * 60;

    public function clearAllCache()
    {
        self::clearAllDetailCache();
    }

    public function clearAllUserCache($user_id, $showMessage = false)
    {
        $this->clearUserCountCache($user_id, $showMessage);
        $this->clearUserListCache($user_id, $showMessage);
    }

    public function clearUserCountCache($user_id, $showMessage = false)
    {
        $cacheKey = self::ALL_NOTIFICATION_COUNT_PREFIX . '_' . $user_id;

        Cache::forget($cacheKey);

        if ($showMessage) {
            echo $cacheKey . PHP_EOL;
        }
    }

    public function clearUserListCache($user_id, $showMessage = false)
    {
        $perPageArray = [10, 20, 30];

        foreach ($perPageArray as $perPage) {
            $lastPage = self::MODEL::query()
                ->where('user_id', $user_id)
                ->active()
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = self::LIST_PREFIX . '_' . $user_id . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }

        Cache::forget(self::LIST_TOKEN);
        Cache::forget(self::LIST_FILTER_TOKEN);
        Cache::forget(self::ADMIN_LIST_TOKEN);
    }

    public function clearAllDetailCache()
    {
        $data = self::MODEL::query()->get();

        foreach ($data as $element) {
            self::clearAdminDetailCacheById($element->id);
            self::clearUserCountCache($element->user_id);
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
}
