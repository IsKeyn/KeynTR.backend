<?php

namespace App\Http\Controllers;

use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class SeriesController extends Controller
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
            'App\Models\Series',
            'App\Filters\SeriesFilter',
            'App\Services\Cache\SeriesCacheService',
            'App\Http\Resources\Series\SeriesListResource',
            ['cover', 'games', 'games.cover']
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            'App\Models\Series',
            'App\Filters\SeriesFilter',
            'App\Services\Cache\SeriesCacheService',
            'App\Services\Game\FilterService'
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            'App\Models\Series',
            'App\Services\Cache\SeriesCacheService',
            'App\Http\Resources\Series\SeriesDetailResource',
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
