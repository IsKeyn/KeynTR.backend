<?php

namespace App\Observers;

use App\Models\Character;
use App\Services\Cache\AdminCacheService;
use App\Services\Cache\GameCacheService;
use App\Services\Observer\DefaultObserverService;

class CharacterObserver
{
    private const CACHE_SERVICE = Character::CACHE_SERVICE;
    private const SERVICE = Character::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Character $character)
    {
        $this->additionalActions($character);

        $this->defaultObserverService->created(
            $character,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Character $character)
    {
        $this->additionalActions($character);

        $this->defaultObserverService->updated(
            $character,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(Character $character)
    {
        $this->additionalActions($character);

        $this->defaultObserverService->deleted(
            $character,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(Character $character)
    {
        $this->additionalActions($character);

        $this->defaultObserverService->restored(
            $character,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(Character $character)
    {
        $this->additionalActions($character);

        $this->defaultObserverService->forceDeleted(
            $character,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($character)
    {
        AdminCacheService::clearAdminAdditionalDataCache();

        $this->clearRelatedCache($character);
    }

    private function clearRelatedCache($character)
    {
        if ($character->games) {
            $entityCacheService = app(GameCacheService::class);

            foreach ($character->games as $item) {
                $entityCacheService->clearDetailCacheBySlug($item->slug);
                $entityCacheService->clearAdminDetailCacheById($item->id);
            }
        }
    }
}
