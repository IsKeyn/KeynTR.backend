<?php

namespace App\Observers;

use App\Events\PlayerData;
use App\Models\User;
use App\Services\Cache\AdminCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Observer\DefaultObserverService;

class UserObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the GamingPlatform "created" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function created(User $user)
    {
        $this->clearRelatedCache($user);

        $this->defaultObserverService->created(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "updated" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function updated(User $user)
    {
        $this->clearRelatedCache($user);

        $this->defaultObserverService->updated(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "deleted" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function deleted(User $user)
    {
        $this->clearRelatedCache($user);

        $this->defaultObserverService->deleted(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "restored" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function restored(User $user)
    {
        $this->clearRelatedCache($user);

        $this->defaultObserverService->restored(
            $user,
            'App\Services\Cache\UserCacheService',
            'App\Services\User\UserService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the GamingPlatform "force deleted" event.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        $this->clearRelatedCache($user);

        $this->defaultObserverService->forceDeleted(
            $user,
            'App\Services\Cache\UserCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    private function clearRelatedCache($user)
    {
        $user->load('bgPlayer', 'bgPlayer.boardGame');

        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $boardGameCacheService = app(BoardGameCacheService::class);

        foreach ($user->bgPlayer as $player) {
            PlayerData::dispatch($player);
            $bgPlayerCacheService->clearClientDetailCache($player);
            $bgPlayerCacheService->clearBgListCache($player->boardGame);

            $boardGameCacheService->clearDetailCacheAllTypes($player->boardGame);
            $boardGameCacheService->clearClientPlayerListCacheFn($player->boardGame->slug, $player->user_id);
        }
    }
}
