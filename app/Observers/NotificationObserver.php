<?php

namespace App\Observers;

use App\Events\NotificationCount;
use App\Events\NotificationCreated;
use App\Models\User\Notification;
use App\Services\Cache\NotificationCacheService;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     *
     * @param  \App\Models\User\Notification  $notification
     * @return void
     */
    public function created(Notification $notification)
    {
        $cacheService = app(NotificationCacheService::class);
        $cacheService->clearAllUserCache($notification->user_id);

        NotificationCount::dispatch($notification->user_id);
        NotificationCreated::dispatch($notification->user_id, $notification);
    }

    /**
     * Handle the Notification "updated" event.
     *
     * @param  \App\Models\User\Notification  $notification
     * @return void
     */
    public function updated(Notification $notification)
    {
        $cacheService = app(NotificationCacheService::class);
        $cacheService->clearAllUserCache($notification->user_id);

        NotificationCount::dispatch($notification->user_id);
        NotificationCreated::dispatch($notification->user_id, $notification);
    }

    /**
     * Handle the Notification "deleted" event.
     *
     * @param  \App\Models\User\Notification  $notification
     * @return void
     */
    public function deleted(Notification $notification)
    {
        $cacheService = app(NotificationCacheService::class);
        $cacheService->clearAllUserCache($notification->user_id);

        NotificationCount::dispatch($notification->user_id);
        NotificationCreated::dispatch($notification->user_id, $notification);
    }

    /**
     * Handle the Notification "restored" event.
     *
     * @param  \App\Models\User\Notification  $notification
     * @return void
     */
    public function restored(Notification $notification)
    {
        $cacheService = app(NotificationCacheService::class);
        $cacheService->clearAllUserCache($notification->user_id);

        NotificationCount::dispatch($notification->user_id);
        NotificationCreated::dispatch($notification->user_id, $notification);
    }

    /**
     * Handle the Notification "force deleted" event.
     *
     * @param  \App\Models\User\Notification  $notification
     * @return void
     */
    public function forceDeleted(Notification $notification)
    {
        $cacheService = app(NotificationCacheService::class);
        $cacheService->clearAllUserCache($notification->user_id);

        NotificationCount::dispatch($notification->user_id);
        NotificationCreated::dispatch($notification->user_id, $notification);
    }
}
