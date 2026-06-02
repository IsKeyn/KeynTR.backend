<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Group;
use App\Services\Entity\EntityService;

class GroupService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Group',
            'App\Services\Cache\GroupCacheService',
            'App\Http\Resources\Admin\Group\DetailResource',
            $id,
            ['tags', 'additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function set($entity, $genres)
    {
        $arGenresIds = [];

        foreach ($genres as $genre) {
            if (isset($genre['group'])) {
                $genreEntity = Group::query()->where('id', $genre['group'])->first();

                if ($genreEntity) {
                    $arGenresIds[] = $genreEntity->id;
                }
            }
        }

        return $entity->groups()->syncWithPivotValues($arGenresIds, ['type' => Game::SERIES_TYPE]);
    }
}
