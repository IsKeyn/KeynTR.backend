<?php
namespace App\Services\Entity;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EntityService
{
    public static function getById(
        $entity,
        $cacheService,
        $resource,
        $id,
        $with = [],
        $forceRefresh = false,
        $withTrashed = false
    )
    {
        $cacheKey = $cacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed, $entity, $with, $resource) {
            $item = $entity::findById($id)
                ->with($with);

            if ($withTrashed) {
                $item->withTrashed();
            }

            $item = $item->first();

            return $resource::make($item);
        };

        // Если передан флаг принудительного обновления, игнорируем кеш
        if ($forceRefresh) {
            return $fetchData();
        }

        return Cache::remember($cacheKey, $cacheService::TIME, $fetchData);
    }

    public static function getListFilters(
        $request,
        $entity,
        $filterClass,
        $cacheService,
        $with = [],
        $useShowInList = false
    ) {
        $cacheKey = $cacheService::FILTER_PREFIX . '_' . $request->filterList;

        if ($request->active) {
            $cacheKey .= '_active';
        }

        $time = $cacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                $cacheService::LIST_FILTER_TOKEN,
                fn() => Str::random(10)
            );

            $filters = json_decode($request->filters, true);
            ksort($filters); // Гарантируем одинаковый порядок ключей
            $cacheKey .= '_' . md5(json_encode($filters)) . '_' . $cacheToken;

            $time = $cacheService::FILTER_TIME;
        }

        return Cache::remember(
            $cacheKey,
            $time,
            function () use ($request, $entity, $with, $filterClass, $useShowInList
            ) {
                if ($request->filterList) {
                    $filterList = json_decode($request->filterList);

                    $withException = ['minMaxData', 'events', 'companies', 'gamePlatforms'];

                    foreach ($filterList as $filterName) {
                        if (array_search($filterName, $withException) === false) {
                            $with[] = $filterName;
                        }
                    }

                    // Получаем список всех игр
                    $items = $entity::query()->with($with);

                    if ($useShowInList) {
                        $items->where('show_in_list', true);
                    }

                    if ($request->active) $items->active();
                    $items = $items->get();

                    $filterServiceObj = app('App\Services\Filter\FilterService');
                    $result = $filterServiceObj->get($items, $filterList);

                    if (
                        $request->filters
                        && $decodedFilters = json_decode($request->filters)
                            && isset($decodedFilters['disableUnusedFilters'])
                            && $decodedFilters['disableUnusedFilters'] === true
                    ) {
                        // Получаем отфильтрованный список игр
                        $filter = new $filterClass($request);
                        $filteredItems = $filter->apply($entity::query())->with($with);

                        if ($useShowInList) {
                            $filteredItems->where('show_in_list', true);
                        }

                        if ($request->active) $filteredItems->active();
                        $filteredItems = $filteredItems->get();

                        $availableFilters = $filterServiceObj->get($filteredItems, $filterList);
                        $result = $filterServiceObj->compareFilters($result, $availableFilters);
                    }

                    return $result;
                }
            });
    }

    public static function sync(
        $entity,
        $items,
        $key,
        $model,
        $relationName
    )
    {
        $arIds = [];

        foreach ($items as $item) {
            if (isset($item[$key])) {
                $entityForSync = $model::query()->where('id', $item[$key])->first();

                if ($entityForSync) {
                    $arIds[] = $entityForSync->id;
                }
            }
        }

        return $entity->$relationName()->sync($arIds);
    }
}
