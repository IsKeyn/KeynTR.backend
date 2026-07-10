<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\BoardGame\AddGame;
use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Models\BoardGame\BoardPositionEffect;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\ShopItem;
use App\Models\BoardGame\StatusEffect;
use App\Models\BoardGame\StatusEffectBind;
use App\Models\BoardGame\Timer;
use App\Models\Company;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Menu;
use App\Models\Movie;
use App\Models\Permission;
use App\Models\Person\Person;
use App\Models\Role;
use App\Models\Series;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Models\User\Notification;
use App\Models\Version;
use App\Observers\ArticleObserver;
use App\Observers\BoardGame\BgAddGameObserver;
use App\Observers\BoardGame\BgPlayerGameObserver;
use App\Observers\BoardGame\BgPlayerInteractionsObserver;
use App\Observers\BoardGame\BgPlayerObserver;
use App\Observers\BoardGame\BgPlayerStatusEffectObserver;
use App\Observers\BoardGame\BgPlayerTimerObserver;
use App\Observers\BoardGame\BgBoardObserver;
use App\Observers\BoardGame\BgGameListObserver;
use App\Observers\BoardGame\BgInventoryObserver;
use App\Observers\BoardGame\BgItemBindObserver;
use App\Observers\BoardGame\BgItemObserver;
use App\Observers\BoardGame\BgLogObserver;
use App\Observers\BoardGame\BgPlayerPositionObserver;
use App\Observers\BoardGame\BgPositionEffectBindObserver;
use App\Observers\BoardGame\BgPositionEffectObserver;
use App\Observers\BoardGame\BgShopItemObserver;
use App\Observers\BoardGame\BgStatusEffectBindObserver;
use App\Observers\BoardGame\BgStatusEffectObserver;
use App\Observers\BoardGame\BoardGameObserver;
use App\Observers\CompanyObserver;
use App\Observers\GameObserver;
use App\Observers\GamingPlatformObserver;
use App\Observers\GenreObserver;
use App\Observers\GroupObserver;
use App\Observers\MenuObserver;
use App\Observers\MovieObserver;
use App\Observers\NotificationObserver;
use App\Observers\PermissionObserver;
use App\Observers\PersonObserver;
use App\Observers\RoleObserver;
use App\Observers\SeriesObserver;
use App\Observers\SettingObserver;
use App\Observers\TagObserver;
use App\Observers\BoardGame\TimerObserver;
use App\Observers\UserObserver;
use App\Observers\VersionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Game::observe(GameObserver::class);
        Movie::observe(MovieObserver::class);
        Series::observe(SeriesObserver::class);
        Person::observe(PersonObserver::class);
        Group::observe(GroupObserver::class);
        GamingPlatform::observe(GamingPlatformObserver::class);
        Genre::observe(GenreObserver::class);
        Company::observe(CompanyObserver::class);
        Version::observe(VersionObserver::class);
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
        Menu::observe(MenuObserver::class);
        Setting::observe(SettingObserver::class);
        Notification::observe(NotificationObserver::class);
        Tag::observe(TagObserver::class);
        Article::observe(ArticleObserver::class);

        BoardGame::observe(BoardGameObserver::class);
        BoardGamePlayer::observe(BgPlayerObserver::class);
        Item::observe(BgItemObserver::class);
        ItemBind::observe(BgItemBindObserver::class);
        ShopItem::observe(BgShopItemObserver::class);
        BoardGameInventory::observe(BgInventoryObserver::class);
        StatusEffect::observe(BgStatusEffectObserver::class);
        StatusEffectBind::observe(BgStatusEffectBindObserver::class);
        PlayerStatusEffect::observe(BgPlayerStatusEffectObserver::class);
        Board::observe(BgBoardObserver::class);
        BoardPositionEffect::observe(BgPositionEffectObserver::class);
        BoardPositionEffectsBind::observe(BgPositionEffectBindObserver::class);
        BoardGamePlayerPosition::observe(BgPlayerPositionObserver::class);
        PlayerInteractions::observe(BgPlayerInteractionsObserver::class);
        BoardGameGameList::observe(BgGameListObserver::class);
        PlayerGame::observe(BgPlayerGameObserver::class);
        AddGame::observe(BgAddGameObserver::class);
        Timer::observe(TimerObserver::class);
        BoardGamePlayerTimer::observe(BgPlayerTimerObserver::class);
        BoardGameLog::observe(BgLogObserver::class);
    }
}
