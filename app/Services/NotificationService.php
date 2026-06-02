<?php

namespace App\Services;

use App\Models\User\Notification;
use App\Services\Cache\NotificationCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\User\Notification',
            'App\Services\Cache\NotificationCacheService',
            'App\Http\Resources\Admin\Notification\DetailResource',
            $id,
            ['tags', 'additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function getCount()
    {
        $userId = Auth::id();

        if (!$userId) return [];

        $cacheKey = NotificationCacheService::ALL_NOTIFICATION_COUNT_PREFIX . '_' . $userId;

        return Cache::remember($cacheKey, NotificationCacheService::TIME, function () use ($userId) {
            $row = DB::table('notifications')
                ->selectRaw('(SELECT COUNT(*) FROM notifications WHERE user_id = ? AND viewed = 0 AND active = 1) as notification_count',
                    [$userId])
                ->selectRaw('(SELECT COUNT(*) FROM messages WHERE user_id = ? AND viewed = 0 AND active = 1) as message_count',
                    [$userId])
                ->first();

            return $row ? [
                'notification_count' => $row->notification_count,
                'message_count' => $row->message_count,
            ] : [];
        });
    }

    public static function set($fields)
    {
        return Notification::create($fields);
    }
}
