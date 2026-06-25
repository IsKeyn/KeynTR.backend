<?php

namespace App\Observers\BoardGame;

use App\Events\TimerStatusToggle;
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
        $this->clearRelatedCache($timer);

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache(false, ['userId' => $timer->user_id]);
        $entityCacheService->clearAdminDetailCacheById($timer->id);
        $entityCacheService->clearDetailCacheBySlug($timer->slug);

        $version = self::SERVICE::getById($timer->id, true)->toArray(request());
        VersionService::set($version, $timer->model, $timer->id, $timer->name, Version::TYPE_CREATE);
    }


    public function updated(Timer $timer)
    {
        $this->clearRelatedCache($timer);

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache(false, ['userId' => $timer->user_id]);
        $entityCacheService->clearAdminDetailCacheById($timer->id);
        $entityCacheService->clearDetailCacheBySlug($timer->slug);

        $version = self::SERVICE::getById($timer->id, true, true)->toArray(request());
        VersionService::set($version, $timer->model, $timer->id, $timer->name, Version::TYPE_UPDATE);

        $timer->load(['playerTimer']);
        TimerStatusToggle::dispatch($timer);
    }

    public function deleted(Timer $timer)
    {
        $this->clearRelatedCache($timer);

        $hasSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($timer)
        );

        if ($hasSoftDeletes && !$timer->isForceDeleting()) {
            $version = self::SERVICE::getById($timer->id, true, true)->toArray(request());
            VersionService::set($version, $timer->model, $timer->id, $timer->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $timer->model)
                ->where('entity_id', $timer->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $timer->model, $timer->id, $timer->name, Version::TYPE_DELETE);
            }

            if (!$hasSoftDeletes) {
                $this->detachRelation($timer);
            }
        }

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache(false, ['userId' => $timer->user_id]);
        $entityCacheService->clearAdminDetailCacheById($timer->id);
        $entityCacheService->clearDetailCacheBySlug($timer->slug);
    }

    public function restored(Timer $timer)
    {
        $this->clearRelatedCache($timer);

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache(false, ['userId' => $timer->user_id]);
        $entityCacheService->clearAdminDetailCacheById($timer->id);
        $entityCacheService->clearDetailCacheBySlug($timer->slug);

        $version = self::SERVICE::getById($timer->id, true, true)->toArray(request());
        VersionService::set($version, $timer->model, $timer->id, $timer->name, Version::TYPE_RECOVERY);
    }

    public function forceDeleted(Timer $timer)
    {
        $this->clearRelatedCache($timer);

        $entityCacheService = app(self::CACHE_SERVICE);
        $entityCacheService->clearListCache(false, ['userId' => $timer->user_id]);

        $this->detachRelation($timer);
    }

    private function detachRelation($entity)
    {
        $entity->playerTimer->each->delete();
    }

    private function clearRelatedCache($timer)
    {

    }
}
