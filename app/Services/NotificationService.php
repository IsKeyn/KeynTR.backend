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

    public static function getCount($userId = null)
    {
        if (!$userId) {
            $userId = Auth::id();
        }

        if (!$userId) return [];

        $cacheKey = NotificationCacheService::ALL_NOTIFICATION_COUNT_PREFIX . '_' . $userId;

        return Cache::remember($cacheKey, NotificationCacheService::TIME, function () use ($userId) {
            $row = DB::table('notifications')
                ->selectRaw('(SELECT COUNT(*) FROM notifications WHERE user_id = ? AND viewed = 0 AND active = 1) as notification_count',
                    [$userId])
                ->first();

            // Один оптимизированный SQL-запрос без создания Eloquent-моделей
            $totalUnread = DB::table('ms_messages')
                ->join('ms_chat_user', function ($join) use ($userId) {
                    $join->on('ms_chat_user.chat_id', '=', 'ms_messages.chat_id')
                        ->where('ms_chat_user.user_id', '=', $userId);
                })
                ->where('ms_messages.user_id', '!=', $userId) // Не считаем свои сообщения
                // COALESCE нужен, чтобы считать null (в новых чатах) как 0
                ->whereRaw('ms_messages.id > COALESCE(ms_chat_user.last_read_message_id, 0)')
                ->count(); // Возвращает целое число (int)

            return $row ? [
                'notification_count' => $row->notification_count,
                'message_count' => $totalUnread,
            ] : [];
        });
    }

    public static function set($fields)
    {
        return Notification::create($fields);
    }
}
