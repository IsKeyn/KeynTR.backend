<?php

namespace App\Http\Controllers;

use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class CompanyController extends Controller
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
            'App\Models\Company',
            'App\Filters\CompanyFilter',
            'App\Services\Cache\CompanyCacheService',
            'App\Http\Resources\Company\ListResource',
            ['cover']
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            'App\Models\Company',
            'App\Filters\CompanyFilter',
            'App\Services\Cache\CompanyCacheService',
            'App\Services\Game\FilterService'
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            'App\Models\Company',
            'App\Services\Cache\CompanyCacheService',
            'App\Http\Resources\Company\DetailResource',
            [
                'views',
                'likes',
                'comments',
            ],
            [
                'game',
                'game.media',
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
