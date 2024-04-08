<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\ViewsLog;
use Google\Service\Blogger\Page;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function index($type)
    {
        if ($type !== 'all') {
            $result = Tag::query()->whereHas($type)->get();
        } else {
            $result = Tag::query()->get();
        }

        return TagResource::collection($result);
    }

    public static function setViews($tags, $userId = null) {
        $tagsList = Tag::query()->whereIn('name', $tags)->get();

        $preparedArray = [];

        if ($tagsList) {
            foreach ($tagsList as $tag) {
                $preparedArray[] = [
                    'entity_type' => get_class($tag),
                    'entity_id' => $tag->id,
                    'created_by' => $userId,
                ];
            }

            ViewsLog::query()->upsert(
                $preparedArray,
                'id',
            );
        }
    }
}
