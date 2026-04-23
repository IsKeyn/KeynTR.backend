<?php

namespace App\Services\Entity;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ViewsLogService;
use App\Http\Resources\UserActions\UserActionsResource;

class DefaultEntityService
{
    public function getList(
        $request,
        $entity,
        $filterClass,
        $cacheService,
        $resource,
        $with = []
    )
    {
        $cacheKey = $cacheService::LIST_PREFIX;
        if ($request->page) $cacheKey .= '_' . $request->page;
        if ($request->perPage) $cacheKey .= '_' . $request->perPage;

        $time = $cacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                $cacheService::LIST_TOKEN,
                fn() => Str::random(10)
            );

            $filters = json_decode($request->filters, true);
            ksort($filters);
            $cacheKey .= '_' . md5(json_encode($filters)) . '_' . $cacheToken;

            $time = $cacheService::FILTER_TIME;
        }

        return Cache::remember(
            $cacheKey,
            $time,
            function () use (
                $request,
                $entity,
                $filterClass,
                $with,
                $resource
        ) {
            $filter = new $filterClass($request);
            $item = $filter
                ->apply($entity::query())
                ->with($with);

            if (method_exists($entity, 'scopeActive')) $item->active();
            if (!isset($request->sort)) $item->orderByRaw('sort IS NULL, sort ASC');

            $result = $item->paginate($request->perPage ? $request->perPage : 10);

            return $resource::collection($result);
        });
    }

    public function getListFilters(
        $request,
        $entity,
        $filterClass,
        $cacheService,
        $filterService,
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
            function () use ($request, $entity, $with, $filterClass, $filterService, $useShowInList
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

                $result = $filterService->get($items, $filterList);

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

                    $availableFilters = $filterService->get($filteredItems, $filterList);
                    $result = $filterService->compareFilters($result, $availableFilters);
                }

                return $result;
            }
        });
    }

    public function get(
        $request,
        $slug,
        $entity,
        $cacheService,
        $resource,
        $shortWith = [],
        $fullWith = []
    ) {
        $item = $entity::findBySlug($slug)->with($shortWith);

        if (!$request->preview) $item->active();
        $item = $item->first();

        if ($item) {
            if (!$request->preview) ViewsLogService::set($request, get_class($item), $item->id);

            $cacheKey = $cacheService::DETAIL_PREFIX . '_' . $slug;

            $data = Cache::remember(
                $cacheKey,
                $cacheService::TIME,
                function () use (
                    $request,
                    $slug,
                    $entity,
                    $fullWith,
                    $resource
            ) {
                $item = $entity::findBySlug($slug)->with($fullWith);

                if (!$request->preview) $item->active();
                $item = $item->first();

                return [
                    ...$resource::make($item)->toArray(request()),
                ];
            });

            return [
                ...$data,
                ...UserActionsResource::make($item)->toArray(request()),
            ];
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }
}
