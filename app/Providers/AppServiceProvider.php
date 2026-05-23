<?php

namespace App\Providers;

use App\Models\Article;
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
    }
}
