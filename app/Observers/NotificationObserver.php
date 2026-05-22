<?php

namespace App\Observers;

use App\Events\NotificationCount;
use App\Events\NotificationCreated;
use App\Models\User\Notification;
use App\Models\Version;
use App\Services\Cache\NotificationCacheService;
use App\Services\NotificationService;
use App\Services\VersionService;

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

        $version = NotificationService::getById($notification->id, true)->toArray(request());
        VersionService::set($version, $notification->model, $notification->id, $notification->name, Version::TYPE_CREATE);
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

        $version = NotificationService::getById($notification->id, true)->toArray(request());
        VersionService::set($version, $notification->model, $notification->id, $notification->name, Version::TYPE_CREATE);
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

        if (!$notification->isForceDeleting()) {
            $version = NotificationService::getById($notification->id, true, true)->toArray(request());
            VersionService::set($version, $notification->model, $notification->id, $notification->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $notification->model)
                ->where('entity_id', $notification->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $notification->model, $notification->id, $notification->name, Version::TYPE_DELETE);
            }
            return;
        }
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

        $version = NotificationService::getById($notification->id, true)->toArray(request());
        VersionService::set($version, $notification->model, $notification->id, $notification->name, Version::TYPE_CREATE);
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
