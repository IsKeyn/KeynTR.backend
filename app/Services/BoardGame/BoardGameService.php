<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Services\Entity\EntityService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class BoardGameService extends ServiceProvider
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGame::class,
            'App\Services\Cache\BoardGame\BoardGameCacheService',
            'App\Http\Resources\Admin\BoardGame\DetailResource',
            $id,
            [
                'settings',
                'tags',
                'additionalFields',
                'media',
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
