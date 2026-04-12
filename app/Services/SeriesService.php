<?php

namespace App\Services;

use App\Models\Series;

class SeriesService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $gameBySlug = Series::findBySlug($fields['slug'])->first();

        if ($gameBySlug) {
            return ErrorService::message('Серия с таким Slug уже существует');
        }

        if ($series = Series::create($fields)) {
            return $series;
        }
    }

    public static function set($entity, $data, $key = 'series')
    {
        $arIds = [];

        foreach ($data as $element) {
            if (isset($element[$key])) {
                $seriesEntity = Series::query()->where('id', $element[$key])->first();

                if ($seriesEntity) {
                    $arIds[] = $seriesEntity->id;
                }
            }
        }

        return $entity->series()->syncWithPivotValues($arIds, []);
    }
}
