<?php

namespace App\Services;


use App\Services\Entity\EntityService;

class MenuService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Menu',
            'App\Services\Cache\MenuCacheService',
            'App\Http\Resources\Admin\Menu\DetailResource',
            $id,
            ['tags', 'additionalFields', 'permissions'],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function set($entity, $items)
    {
        return EntityService::sync(
            $entity,
            $items,
            'menus',
            'App\Models\Menu',
            'menus',
        );
    }
}
