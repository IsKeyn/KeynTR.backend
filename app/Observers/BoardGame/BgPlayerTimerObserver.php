<?php

namespace App\Observers\BoardGame;

use App\Events\TimerStatusToggle;
use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Models\Version;
use App\Services\Observer\DefaultObserverService;
use App\Services\VersionService;

class BgPlayerTimerObserver
{
    private const CACHE_SERVICE = 'App\Services\Cache\BgPlayerTimerCacheService';
    private const SERVICE = 'App\Services\BoardGame\BgTimerTimerService';

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardGamePlayerTimer $boardGamePlayerTimer)
    {
        $this->defaultObserverService->created(
            $boardGamePlayerTimer,
            self::CACHE_SERVICE,
            self::SERVICE
        );

        $boardGamePlayerTimer->load(['timer.boardGame']);
        TimerStatusToggle::dispatch($boardGamePlayerTimer);
    }

    public function updated(BoardGamePlayerTimer $boardGamePlayerTimer)
    {
        $entity = $boardGamePlayerTimer;

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache();
        $entityCacheService->clearAdminDetailCacheById($entity->id);
        $entityCacheService->clearDetailCacheBySlug($entity->slug);

        $version = self::SERVICE::getById($entity->id, true, false)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_UPDATE);

        $boardGamePlayerTimer->load(['timer.boardGame']);
        TimerStatusToggle::dispatch($boardGamePlayerTimer);
    }

    public function deleted(BoardGamePlayerTimer $boardGamePlayerTimer)
    {
        $entity = $boardGamePlayerTimer;

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

    public function restored(BoardGamePlayerTimer $boardGamePlayerTimer)
    {
        $entity = $boardGamePlayerTimer;

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache();
        $entityCacheService->clearAdminDetailCacheById($entity->id);
        $entityCacheService->clearDetailCacheBySlug($entity->slug);

        $version = self::SERVICE::getById($entity->id, true, false)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_RECOVERY);
    }

    public function forceDeleted(BoardGamePlayerTimer $boardGamePlayerTimer)
    {
        $this->defaultObserverService->forceDeleted(
            $boardGamePlayerTimer,
            self::CACHE_SERVICE
        );
    }
}
