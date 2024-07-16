<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Tag;
use App\Models\ViewsLog;
use App\Models\VotesCount;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
//    const RELATION = [
//        'views',
//        'likes',
//    ];

    public function getByFilter(Request $request)
    {
        $mediaQuery = Media::query()->with(['views', 'likes']);

        if ($filter = $request->filter) {
            $mediaQuery->when(isset($filter['group_id']), function ($query) use ($filter, $request) {
                $query->whereHas('group', function ($q) use ($filter) {
                    $q->where('media_groups.id', $filter['group_id']);
                });
            });

            $mediaQuery->when(isset($filter['tags']), function ($query) use ($filter, $request) {
                $query->whereHas('tags', function ($q) use ($filter, $request) {
                    $q->whereIn('name', $filter['tags']);

                    $user = $request->user();
                    TagsController::setViews($filter['tags'], $user ? $user->id : null);
                });
            });

            $mediaQuery->when(isset($filter['sort']), function ($query) use ($filter) {
//                if (in_array($filter['sort']['field'],self::RELATION)) {
//                    $query->whereHas($filter['sort']['field'], function($query) use ($filter)
//                    {
//                        $query->orderBy('value', $filter['sort']['sort']);
//                    });
//                } else {
                    $query->orderBy($filter['sort']['field'], $filter['sort']['sort']);
//                }

                // Нааиболее обсуждаемые
            });

            // Выьирает только с просмотрами
//            $mediaQuery->whereHas('views', function($query) use ($filter)
//            {
//                $query->orderBy('value');
//            });

            $mediaQuery->orderBy('created_at', 'desc');
        } else {
            $mediaQuery->orderBy('created_at', 'desc');
        }

//        dd($mediaQuery);

//        $mediaQuery->orderBy(VotesCount::select('value')
//            ->whereColumn('votes_counts.entity_id', 'media.id')
//            ->where('votes_counts.entity_type', Media::class)
//            ->latest()
//            ->take(1), 'asc');

//        $mediaQuery->inRandomOrder();

        $result = $mediaQuery->paginate($request->perPage ?? 4);
//        $result = $mediaQuery->get();


//        dd($result->count());


//        $perPage = $request->perPage ?? 4;
//        $offsetPages = $request->input('page', 1) - 1;

//        dd($result->toArray());

//        $result = array_slice(
//            $result->toArray(),
//            $offsetPages * $perPage,
//            $perPage
//        );
//
        return MediaResource::collection($result);
    }

    public function mediaById(Media $media, Request $request)
    {
        ViewsLogService::set($request, $media->model, $media->id);

        return MediaResource::make($media);
    }
}
