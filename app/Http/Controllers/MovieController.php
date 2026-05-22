<?php

namespace App\Http\Controllers;

use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class MovieController extends Controller
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
            'App\Models\Movie',
            'App\Filters\MovieFilter',
            'App\Services\Cache\MovieCacheService',
            'App\Http\Resources\Movie\ListResource',
            ['media', 'genres', 'dates']
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            'App\Models\Movie',
            'App\Filters\MovieFilter',
            'App\Services\Cache\MovieCacheService',
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            'App\Models\Movie',
            'App\Services\Cache\MovieCacheService',
            'App\Http\Resources\Movie\DetailResource',
            [
                'views',
                'likes',
                'comments',
            ],
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
            ]
        );
    }
}
