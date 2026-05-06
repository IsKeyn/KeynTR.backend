<?php

namespace App\Http\Controllers;

use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class GenreController extends Controller
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
            'App\Models\Genre',
            'App\Filters\GenreFilter',
            'App\Services\Cache\GenreCacheService',
            'App\Http\Resources\Genre\ListResource',
            ['cover']
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            'App\Models\Genre',
            'App\Filters\GenreFilter',
            'App\Services\Cache\GenreCacheService',
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            'App\Models\Genre',
            'App\Services\Cache\GenreCacheService',
            'App\Http\Resources\Genre\DetailResource',
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
