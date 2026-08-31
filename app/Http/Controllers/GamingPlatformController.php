<?php

namespace App\Http\Controllers;

use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class GamingPlatformController extends Controller
{
    protected DefaultEntityService $defaultEntityService;

    public function __construct(DefaultEntityService $defaultEntityService)
    {
        $this->defaultEntityService = $defaultEntityService;
    }

    public function getList(Request $request)
    {
        return $this->defaultEntityService->getList(
            $request,
            'App\Models\GamingPlatform',
            'App\Filters\GamingPlatformFilter',
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Http\Resources\GamingPlatform\ListResource',
            ['cover']
        );
    }

    public function getShortList(Request $request)
    {
        return $this->defaultEntityService->getList(
            $request,
            'App\Models\GamingPlatform',
            'App\Filters\GamingPlatformFilter',
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Http\Resources\GamingPlatform\ForSelectResource',
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            'App\Models\GamingPlatform',
            'App\Filters\GamingPlatformFilter',
            'App\Services\Cache\GamingPlatformCacheService',
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            'App\Models\GamingPlatform',
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Http\Resources\GamingPlatform\DetailResource',
            [
                'views',
                'likes',
                'comments',
            ],
            [
                'games',
                'games.media',
                'titleImage',
                'cover',
                'tags',
                'additionalFields',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ]
        );
    }
}
