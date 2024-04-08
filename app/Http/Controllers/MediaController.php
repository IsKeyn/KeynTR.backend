<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Tag;
use App\Models\ViewsLog;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function mediaByFilter(Request $request)
    {
        $mediaQuery = Media::query();

        if ($filter = $request->filter) {
            $mediaQuery->when(isset($filter['tags']), function ($query) use ($filter, $request) {
                $query->whereHas('tags', function ($q) use ($filter, $request) {
                    $q->whereIn('name', $filter['tags']);

                    $user = $request->user();
                    TagsController::setViews($filter['tags'], $user ? $user->id : null);
                });
            });

            $mediaQuery->when(isset($filter['sort']), function ($query) use ($filter) {
                $query->orderBy($filter['sort']['field'], $filter['sort']['sort']);
            });
        } else {
            $mediaQuery->orderBy('created_at', 'desc');
        }

//        $mediaQuery->inRandomOrder();

        $result = $mediaQuery->paginate($request->perPage ?? 4);

        return MediaResource::collection($result);
    }

    public function mediaById(Media $media, Request $request)
    {
        ViewsLogService::set($request, $media->model, $media->id);

        return MediaResource::make($media);
    }
}
