<?php

namespace App\Services\Entity;

use App\Models\Version;
use App\Services\RelatedDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DefaultAdminEntityService
{
    public function index(
        $request,
        $entity,
        $cacheService,
        $filterClass,
        $resource,
        $hasSortField = true,
        $with = []
    )
    {
        $cacheKey = $cacheService::ADMIN_LIST_PREFIX . '_' . $request->page . '_' . $request->perPage;
        $time = $cacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                $cacheService::ADMIN_LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = $cacheService::FILTER_TIME;
        }

        return Cache::remember(
            $cacheKey,
            $time,
            function () use (
                $request,
                $entity,
                $filterClass,
                $resource,
                $hasSortField,
                $with
        ) {
            $filter = new $filterClass($request);
            $elementsList = $filter->apply($entity::query())->with($with);

            if (!isset($request->sort) && $hasSortField) {
                $elementsList->orderByRaw('sort IS NULL, sort ASC')->orderBy('id', 'asc');
            }

            $result = $elementsList->paginate($request->perPage ? $request->perPage : 10);

            return $resource::collection($result);
        });
    }

    public function store(
        $request,
        $entity
    )
    {
        $validated = $request->validated();

        if (!isset($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        if (!isset($validated['created_at'])) {
            unset($validated['created_at']);
        }

        if ($item = $entity::create($validated)) {
            $relatedDataService = app(RelatedDataService::class);
            $relatedDataService->set($item, $validated);

            return $item;
        }
    }

    public function update(
        $request,
        $entity
    ) : Bool
    {
        $validated = $request->validated();

        $entity->fill($validated);

        /*
         * Код отвечает за то, чтобы Observer updated сработал только 1 раз, не зависимо от того, было обновления
         * основной таблицы или же обновились связи
         */
        $attributesChanged = $entity->isDirty();

        if ($attributesChanged) {
            $entity->save();
        }

        $relatedDataService = app(RelatedDataService::class);
        $relatedDataService->set($entity, $validated);

        if (!$attributesChanged) {
            $entity->touch();
        }

        return true;
    }

    public function edit(
        $request,
        $id,
        $service
    )
    {
        if ($request->version_id) {
            return Version::findById($request->version_id)->first();
        } else {
            return $service::getById($id);
        }
    }

    public function destroy(
        $entity
    )
    {
        return $entity->delete();
    }

    public function forceDelete(
        $entity,
        $id
    )
    {
        $element = $entity::findById($id)->withTrashed()->first();
        if (!$element) return false;

        return $element->forceDelete();
    }

    public function recovery(
        $entity,
        $id
    )
    {
        $element = $entity::findById($id)->withTrashed()->first();
        if (!$element) return false;

        return $element->restore();
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
}
