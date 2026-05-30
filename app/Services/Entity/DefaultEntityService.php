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

        if (!$request->fullList) {
            if ($request->page) $cacheKey .= '_' . $request->page;
            if ($request->perPage) $cacheKey .= '_' . $request->perPage;
        }

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

            $result = $request->fullList ? $item->get() : $item->paginate($request->perPage ? $request->perPage : 10);

            return $resource::collection($result);
        });
    }

    public function getListFilters(
        $request,
        $entity,
        $filterClass,
        $cacheService,
        $with = [],
        $useShowInList = false
    ) {
        return EntityService::getListFilters(
            $request,
            $entity,
            $filterClass,
            $cacheService,
            $with ,
            $useShowInList
        );
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
