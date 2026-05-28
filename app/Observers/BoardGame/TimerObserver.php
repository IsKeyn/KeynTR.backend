<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\Timer;
use App\Models\Version;
use App\Services\Observer\DefaultObserverService;
use App\Services\VersionService;

class TimerObserver
{
    private const CACHE_SERVICE = 'App\Services\Cache\TimerCacheService';
    private const SERVICE = 'App\Services\BoardGame\TimerService';

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Timer $timer)
    {
        $this->defaultObserverService->created(
            $timer,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Timer $timer)
    {
        $entity = $timer;

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache();
        $entityCacheService->clearAdminDetailCacheById($entity->id);
        $entityCacheService->clearDetailCacheBySlug($entity->slug);

        $version = self::SERVICE::getById($entity->id, true, false)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_UPDATE);
    }

    public function deleted(Timer $timer)
    {
        $entity = $timer;

        if (!$entity->isForceDeleting()) {
            $version = self::SERVICE::getById($entity->id, true, false)->toArray(request());
            VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $entity->model)
                ->where('entity_id', $entity->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $entity->model, $entity->id, $entity->name, Version::TYPE_DELETE);
            }
            return;
        }

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache();
        $entityCacheService->clearAdminDetailCacheById($entity->id);
        $entityCacheService->clearDetailCacheBySlug($entity->slug);
    }

    public function restored(Timer $timer)
    {
        $entity = $timer;

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache();
        $entityCacheService->clearAdminDetailCacheById($entity->id);
        $entityCacheService->clearDetailCacheBySlug($entity->slug);

        $version = self::SERVICE::getById($entity->id, true, false)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_RECOVERY);
    }

    public function forceDeleted(Timer $timer)
    {
        $this->defaultObserverService->forceDeleted(
            $timer,
            self::CACHE_SERVICE
        );
    }
}
