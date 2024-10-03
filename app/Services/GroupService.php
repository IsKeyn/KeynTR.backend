<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Group;

class GroupService
{
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
