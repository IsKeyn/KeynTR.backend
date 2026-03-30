<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Version;
use App\Observers\CompanyObserver;
use App\Observers\GameObserver;
use App\Observers\GamingPlatformObserver;
use App\Observers\GenreObserver;
use App\Observers\GroupObserver;
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
        Group::observe(GroupObserver::class);
        GamingPlatform::observe(GamingPlatformObserver::class);
        Genre::observe(GenreObserver::class);
        Company::observe(CompanyObserver::class);
        Version::observe(VersionObserver::class);
    }
}
