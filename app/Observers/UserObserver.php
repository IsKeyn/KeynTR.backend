<?php

namespace App\Observers;

use App\Events\PlayerData;
use App\Models\BoardGame\ItemBind;
use App\Models\User;
use App\Services\Cache\AdminCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use App\Services\Cache\BoardGame\BgShopItemCacheService;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Observer\DefaultObserverService;
use Illuminate\Support\Facades\Cache;

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
        $this->additionalActions($user);

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
        $this->additionalActions($user);

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
        $this->additionalActions($user);

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
        $this->additionalActions($user);

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
        $this->additionalActions($user);

        $this->defaultObserverService->forceDeleted(
            $user,
            'App\Services\Cache\UserCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    private function additionalActions($user)
    {
        $user->load(['bgPlayer', 'bgPlayer.boardGame', 'bgGamesList', 'bgGamesList.boardGame', 'bgGamesList.game']);
        $this->clearRelatedCache($user);
    }

    private function clearRelatedCache($user)
    {
        $bgPlayerCacheService = app(BgPlayerCacheService::class);
        $boardGameCacheService = app(BoardGameCacheService::class);
        $bgShopItemCacheService = app(BgShopItemCacheService::class);

        foreach ($user->bgPlayer as $player) {
            PlayerData::dispatch($player);
            $bgPlayerCacheService->clearClientDetailCache($player);
            $bgPlayerCacheService->clearBgListCache($player->boardGame);

            $boardGameCacheService->clearDetailCacheAllTypes($player->boardGame);
            $boardGameCacheService->clearClientPlayerListCacheFn($player->boardGame->slug, $player->user_id);
            $bgPlayerCacheService->clearAllGameHistoryCache($player->boardGame);

            // Очищаем кеш магазина ивента
            if ($user->wasChanged('public_name')) {
                $cacheKey = $bgShopItemCacheService::LIST_PREFIX . '_' . $player->boardGame->slug . '_' . ItemBind::class;
                Cache::forget($cacheKey);
            }
        }

        foreach ($user->bgGamesList as $bgGamesList) {
            $bgPlayerCacheService->clearActionsWithGameList($bgGamesList);
        }
    }
}
