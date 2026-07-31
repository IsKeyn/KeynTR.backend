<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    private const MODEL = Character::class;
    private const CACHE_SERVICE = Character::CACHE_SERVICE;
    private const FILTER = Character::FILTER;

    protected DefaultEntityService $defaultEntityService;

    public function __construct(DefaultEntityService $defaultEntityService)
    {
        $this->defaultEntityService = $defaultEntityService;
    }

    public function getList(Request $request)
    {
        return $this->defaultEntityService->getList(
            $request,
            self::MODEL,
            self::FILTER,
            self::CACHE_SERVICE,
            'App\Http\Resources\Character\ListResource',
            [
                'titleImage',
                'cover' => function ($query) {
                    $query->orderByPivot('sort');
                },
            ]
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            self::MODEL,
            self::FILTER,
            self::CACHE_SERVICE,
        );
    }

    public function get(Request $request, $slug)
    {
        return $this->defaultEntityService->get(
            $request,
            $slug,
            self::MODEL,
            self::CACHE_SERVICE,
            'App\Http\Resources\Character\DetailResource',
            [
                'views',
                'likes',
                'comments',
            ],
            [
                'games' => function ($query) {
                    $query->active();
                },
                'games.media',
                'titleImage',
                'cover' => function ($query) {
                    $query->orderByPivot('sort');
                },
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
