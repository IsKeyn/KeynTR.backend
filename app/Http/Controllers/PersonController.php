<?php

namespace App\Http\Controllers;

use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class PersonController extends Controller
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
            'App\Models\Person\Person',
            'App\Filters\PersonFilter',
            'App\Services\Cache\PersonCacheService',
            'App\Http\Resources\Person\PersonListResource',
            ['titleImage', 'cover']
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            'App\Models\Person\Person',
            'App\Filters\PersonFilter',
            'App\Services\Cache\PersonCacheService',
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            'App\Models\Person\Person',
            'App\Services\Cache\PersonCacheService',
            'App\Http\Resources\Person\PersonDetailResource',
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
