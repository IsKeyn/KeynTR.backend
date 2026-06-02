<?php

namespace App\Services;

use App\Services\Entity\EntityService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class MovieService extends ServiceProvider
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Movie',
            'App\Services\Cache\MovieCacheService',
            'App\Http\Resources\Admin\Movie\DetailResource',
            $id,
            [
                'titleImage',
                'cover',
                'dates',
                'anonsDates',
                'tags',
                'series',
                'people',
                'groups',
                'genres',
                'company',
                'company.group',
                'link',
                'additionalFields',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }
}
