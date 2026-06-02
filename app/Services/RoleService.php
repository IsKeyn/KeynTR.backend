<?php

namespace App\Services;


use App\Services\Entity\EntityService;

class RoleService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Role',
            'App\Services\Cache\RoleCacheService',
            'App\Http\Resources\Admin\Role\DetailResource',
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
            'roles',
            'App\Models\Role',
            'roles',
        );
    }
}
