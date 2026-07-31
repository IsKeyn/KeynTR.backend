<?php

namespace App\Services\Messenger;

use App\Events\NotificationCount;
use App\Models\Messenger\Message;
use App\Services\Cache\NotificationCacheService;
use App\Services\Entity\EntityService;

class MessageService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Message::class,
            Message::CACHE_SERVICE,
            Message::DETAIL_RESOURCE,
            $id,
            [
                'tags',
                'additionalFields',
                'media',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function notificationSend($userId)
    {
        $cacheService = app(NotificationCacheService::class);
        $cacheService->clearAllUserCache($userId);
        NotificationCount::dispatch($userId);
    }
}
