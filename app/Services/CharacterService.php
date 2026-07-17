<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Group;
use App\Services\Entity\EntityService;

class CharacterService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $itemBySlug = Character::findBySlug($fields['slug'])->first();

        if ($itemBySlug) {
            return ErrorService::message('Персонаж с таким Slug уже существует');
        }

        if ($item = Character::create($fields)) {
            return $item;
        }
    }

    public static function set($entity, $data, $key = 'character')
    {
        $arIds = [];

        foreach ($data as $element) {
            if (isset($element[$key])) {
                $elementEntity = Character::query()->where('id', $element[$key])->first();

                if ($elementEntity) {
                    // Добавляем person_role если указан
                    if (isset($element['character_role'])) {
                        $groupEntity = Group::query()->where('id', $element['character_role'])->first();

                        if ($groupEntity) {
                            $elementEntity
                                ->group($entity->id, get_class($entity))
                                ->syncWithPivotValues(
                                    $groupEntity->id,
                                    [
                                        'first_b_id' => $entity->id,
                                        'first_b_type' => get_class($entity)
                                    ]
                                );
                        }
                    }

                    $arIds[] = $elementEntity->id;
                }
            }
        }

        return $entity->characters()->syncWithPivotValues($arIds, []);
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Character::class,
            Character::CACHE_SERVICE,
            Character::DETAIL_RESOURCE,
            $id,
            [
                'titleImage',
                'cover',
                'tags',
                'additionalFields',
                'media',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }
}
