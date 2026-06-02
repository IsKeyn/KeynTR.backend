<?php

namespace App\Http\Controllers;

use App\Http\Resources\Media\MediaDetailResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\UserActions\UserActionsResource;
use App\Models\Media;
use App\Services\Cache\MediaCacheService;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

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
                $query->whereHas('groups', function ($q) use ($filter) {
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

    public function mediaById($id, Request $request)
    {
        $media = Media::findById($id)
            ->with([
                'views',
                'likes',
                'comments',
            ])
            ->first();

        if ($media) {
            $cacheKey = MediaCacheService::DETAIL_PREFIX . '_' . $id;

            ViewsLogService::set($request, $media->model, $media->id);

            $data = Cache::remember($cacheKey, MediaCacheService::TIME, function () use ($request, $id) {
                $media = Media::findById($id)
                    ->with([
                        'tags',
                        'user',
                    ])
                    ->first();

                return MediaDetailResource::make($media);
            });

            return [
                ...$data->toArray(request()),
                ...UserActionsResource::make($media)->toArray(request())
            ];
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }
}
