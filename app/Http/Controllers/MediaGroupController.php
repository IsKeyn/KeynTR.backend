<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Http\Resources\TagResource;
use App\Models\MediaGroup;
use Illuminate\Http\Request;

class MediaGroupController extends Controller
{
    public function getByFilter(Request $request)
    {
        $returnData = collect([]);

        if ($request->filter && $request->filter['group_id']) {
            $groupsId = [];

            if (is_array($request->filter['group_id'])) {
                $groupsId = $request->filter['group_id'];
            } else {
                $groupsId[] = $request->filter['group_id'];
            }

            $result = MediaGroup::query()->whereIn('id', $groupsId)->get();

            $mergedCollection = collect([]);

            foreach ($result as $group) {
                $mergedCollection = $mergedCollection->merge($group->mediaGroup)->unique('id');
            }

            /* Получаем теги текущей группы */
            $tags = collect([]);

            foreach ($mergedCollection as $media) {
                $tags = $tags->merge($media->tags)->unique('id');
            }

            $filter = $request->filter;

            /* Фильтрация по тегам */
            if ($filter && isset($filter['tags'])) {
                $mergedCollection = $mergedCollection->filter(function ($media) use ($filter) {
                    if ($media->tags->filter(fn ($tag) => in_array($tag->name, $filter['tags']))->count() > 0) {
                        return true;
                    }
                });
            }

            /* Сделать сортировку */
            if ($filter && isset($filter['sort'])) {
                $mergedCollection = $mergedCollection->sortBy(
                    [
                        [$filter['sort']['field'], $filter['sort']['sort']],
                    ]
                );
            } else {
                $mergedCollection = $mergedCollection->sortBy('pivot.sort');
            }

            $returnData = $mergedCollection;
        }

        $currentPage = $request->page;
        $perPage = $request->perPage ?? 4;

        $currentItems = $returnData->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return response()->json([
            'data' => MediaResource::collection($currentItems),
            'tags' => TagResource::collection($tags),
            'meta' => [
                'total' => $returnData->count(),
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => ceil($returnData->count() / $perPage),
            ],
        ]);

        return MediaResource::collection($currentItems);
    }
}
