<?php

namespace App\Services;

use App\Http\Resources\Admin\Person\AdminPersonResource;
use App\Models\Group;
use App\Models\Person\Person;
use App\Services\Cache\PersonCacheService;
use Illuminate\Support\Facades\Cache;

class PersonService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $itemBySlug = Person::findBySlug($fields['slug'])->first();

        if ($itemBySlug) {
            return ErrorService::message('Персона с таким Slug уже существует');
        }

        if ($item = Person::create($fields)) {
            return $item;
        }
    }

    public static function set($entity, $data, $key = 'people')
    {
        $arIds = [];

        foreach ($data as $element) {
            if (isset($element[$key])) {
                $elementEntity = Person::query()->where('id', $element[$key])->first();

                if ($elementEntity) {
                    // Добавляем person_role если указан
                    if (isset($element['person_role'])) {
                        $groupEntity = Group::query()->where('id', $element['person_role'])->first();

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

        return $entity->people()->syncWithPivotValues($arIds, []);
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        $cacheKey = PersonCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed) {
            $item = Person::findById($id)
                ->with([
                    'titleImage',
                    'cover',
                    'tags',
                    'game',
                    'additionalFields',
                    'seo',
                    'seo.entity',
                    'seo.entity.tags',
                ]);

            if ($withTrashed) {
                $item->withTrashed();
            }

            $item = $item->first();

            return AdminPersonResource::make($item);
        };

        // Если передан флаг принудительного обновления, игнорируем кеш
        if ($forceRefresh) {
            return $fetchData();
        }

        return Cache::remember($cacheKey, PersonCacheService::TIME, $fetchData);
    }
}
