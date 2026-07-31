<?php

namespace App\Services\Cache\Messenger;

use App\Models\Messenger\Chat;
use App\Models\User;
use App\Services\Cache\BaseCacheService;
use Illuminate\Support\Facades\Cache;

class ChatCacheService extends BaseCacheService
{
    public const NAME = Chat::CACHE_NAME;
    public const MODEL = Chat::class;

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

    public function clearUserListCache($user)
    {
        $cacheKey = ChatCacheService::LIST_PREFIX . '_' . $user->id;
        Cache::forget($cacheKey);

        $arUsers = User::query()->active()->select('id')->get();

        foreach ($arUsers as $chatUser) {
            Cache::forget($cacheKey . '_' . $chatUser->id);
        }
    }
}
