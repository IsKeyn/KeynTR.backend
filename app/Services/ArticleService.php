<?php

namespace App\Services;

use App\Services\Entity\EntityService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class ArticleService extends ServiceProvider
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Article',
            'App\Services\Cache\ArticleCacheService',
            'App\Http\Resources\Admin\Article\DetailResource',
            $id,
            [
                'author',
                'articleEditor',
                'tags',
                'additionalFields',
                'people',
                'people',
                'titleImage',
                'cover',
                'company',
                'company.group',
                'link',
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
