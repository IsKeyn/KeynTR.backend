<?php

namespace App\Services;


use App\Services\Entity\EntityService;

class PermissionService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Permission',
            'App\Services\Cache\PermissionCacheService',
            'App\Http\Resources\Admin\Permission\DetailResource',
            $id,
            ['tags', 'additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function set($entity, $items)
    {
        return EntityService::sync(
            $entity,
            $items,
            'permissions',
            'App\Models\Permission',
            'permissions',
        );
    }
}
