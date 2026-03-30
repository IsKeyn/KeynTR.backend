<?php

namespace App\Services;

use App\Models\Series;

class SeriesService
{
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
