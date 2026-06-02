<?php

namespace App\Services\Observer;

use App\Models\Version;
use App\Services\VersionService;

class DefaultObserverService
{
    public function created(
        $entity,
        $cacheServiceClass,
        $service
    )
    {
        $entityCacheService = app($cacheServiceClass);
        $entityCacheService->clearListCache();
        $entityCacheService->clearDetailCacheAllTypes($entity);

        $version = $service::getById($entity->id, true)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_CREATE);
    }

    public function updated(
        $entity,
        $cacheServiceClass,
        $service,
        $withTrashed = true
    )
    {
        $entityCacheService = app($cacheServiceClass);
        $entityCacheService->clearListCache();
        $entityCacheService->clearDetailCacheAllTypes($entity);

        $version = $service::getById($entity->id, true, $withTrashed)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_UPDATE);
    }

    public function deleted(
        $entity,
        $cacheServiceClass,
        $service,
        $withTrashed = true
    )
    {
        $hasSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($entity)
        );

        if ($hasSoftDeletes && !$entity->isForceDeleting()) {
            $version = $service::getById($entity->id, true, $withTrashed)->toArray(request());
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

            if (!$hasSoftDeletes) {
                $this->detachRelation($entity);
            }
        }

        $entityCacheService = app($cacheServiceClass);
        $entityCacheService->clearListCache();
        $entityCacheService->clearDetailCacheAllTypes($entity);
    }

    public function restored(
        $entity,
        $cacheServiceClass,
        $service,
        $withTrashed = true
    )
    {
        $entityCacheService = app($cacheServiceClass);
        $entityCacheService->clearListCache();
        $entityCacheService->clearDetailCacheAllTypes($entity);

        $version = $service::getById($entity->id, true, $withTrashed)->toArray(request());
        VersionService::set($version, $entity->model, $entity->id, $entity->name, Version::TYPE_RECOVERY);
    }

    public function forceDeleted(
        $entity,
        $cacheServiceClass
    )
    {
        $entityCacheService = app($cacheServiceClass);
        $entityCacheService->clearListCache();

        $this->detachRelation($entity);
    }

    /**
     * Удаление связей
     */
    private function detachRelation($entity)
    {
        $entity->tags()->detach();

        $entity->additionalFields()->delete();
        $entity->comments()->delete();
        $entity->views()->delete();
        $entity->likes()->delete();
        $entity->seo()->delete();
    }
}
