<?php

namespace App\Services;

use App\Http\Resources\Admin\AdminSeriesResource;
use App\Models\Series;
use App\Services\Cache\SeriesCacheService;
use Illuminate\Support\Facades\Cache;

class SeriesService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $itemBySlug = Series::findBySlug($fields['slug'])->first();

        if ($itemBySlug) {
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
                $elementEntity = Series::query()->where('id', $element[$key])->first();

                if ($elementEntity) {
                    $arIds[] = $elementEntity->id;
                }
            }
        }

        return $entity->series()->syncWithPivotValues($arIds, []);
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        $cacheKey = SeriesCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed) {
            $item = Series::findById($id)
                ->with([
                    'tags',
                    'game',
                    'genres',
                    'company',
                    'company.group',
                    'link',
                    'additionalFields',
                    'seo',
                    'seo.entity',
                    'seo.entity.tags',
                ]);

            if ($withTrashed) {
                $item->withTrashed();
            }

            $item = $item->first();

            return AdminSeriesResource::make($item);
        };

        // Если передан флаг принудительного обновления, игнорируем кеш
        if ($forceRefresh) {
            return $fetchData();
        }

        return Cache::remember($cacheKey, SeriesCacheService::TIME, $fetchData);
    }
}
