<?php

namespace App\Observers;

use App\Models\Person\Person;
use App\Models\Version;
use App\Services\Cache\GameCacheService;
use App\Services\Cache\PersonCacheService;
use App\Services\PersonService;
use App\Services\VersionService;

class PersonObserver
{
    /**
     * Handle the Person "created" event.
     *
     * @param  \App\Models\Person  $person
     * @return void
     */
    public function created(Person $person)
    {
        $personCacheService = app(PersonCacheService::class);
        $personCacheService->clearListCache();
        $personCacheService->clearAdminDetailCacheById($person->id);
        $personCacheService->clearDetailCacheBySlug($person->slug);

        $version = PersonService::getById($person->id, true)->toArray(request());
        VersionService::set($version, $person->model, $person->id, $person->name, Version::TYPE_CREATE);
    }

    /**
     * Handle the Person "updated" event.
     *
     * @param  \App\Models\Person  $person
     * @return void
     */
    public function updated(Person $person)
    {
        $personCacheService = app(PersonCacheService::class);
        $personCacheService->clearListCache();
        $personCacheService->clearAdminDetailCacheById($person->id);
        $personCacheService->clearDetailCacheBySlug($person->slug);

        $version = PersonService::getById($person->id, true, true)->toArray(request());
        VersionService::set($version, $person->model, $person->id, $person->name, Version::TYPE_UPDATE);

        if ($person->games) {
            $entityCacheService = app(GameCacheService::class);

            foreach ($person->games as $item) {
                $entityCacheService->clearDetailCacheBySlug($item->slug);
                $entityCacheService->clearAdminDetailCacheById($item->id);
            }
        }
    }

    /**
     * Handle the Person "deleted" event.
     *
     * @param  \App\Models\Person  $person
     * @return void
     */
    public function deleted(Person $person)
    {
        if (!$person->isForceDeleting()) {
            $version = PersonService::getById($person->id, true, true)->toArray(request());
            VersionService::set($version, $person->model, $person->id, $person->name, Version::TYPE_SOFT_DELETE);
        } else {
            $lastVersion = Version::query()
                ->where('entity_type', $person->model)
                ->where('entity_id', $person->id)
                ->latest()
                ->first();

            if ($lastVersion) {
                VersionService::set($lastVersion->data, $person->model, $person->id, $person->name, Version::TYPE_DELETE);
            }
            return;
        }

        $personCacheService = app(PersonCacheService::class);
        $personCacheService->clearListCache();
        $personCacheService->clearAdminDetailCacheById($person->id);
        $personCacheService->clearDetailCacheBySlug($person->slug);
    }

    /**
     * Handle the Person "restored" event.
     *
     * @param  \App\Models\Person  $person
     * @return void
     */
    public function restored(Person $person)
    {
        $personCacheService = app(PersonCacheService::class);
        $personCacheService->clearListCache();
        $personCacheService->clearAdminDetailCacheById($person->id);
        $personCacheService->clearDetailCacheBySlug($person->slug);

        $version = PersonService::getById($person->id, true, true)->toArray(request());
        VersionService::set($version, $person->model, $person->id, $person->name, Version::TYPE_RECOVERY);
    }

    /**
     * Handle the Person "force deleted" event.
     *
     * @param  \App\Models\Person  $person
     * @return void
     */
    public function forceDeleted(Person $person)
    {
        $personCacheService = app(PersonCacheService::class);
        $personCacheService->clearListCache();

        // Удаление связей
        $person->tags()->detach();

        $person->additionalFields()->delete();
        $person->comments()->delete();
        $person->views()->delete();
        $person->likes()->delete();
        $person->seo()->delete();
    }
}
