<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SlideResource;
use App\Models\Media;
use App\Models\Slide;
use App\Services\TagService;
use Illuminate\Http\Request;

class AdminSlideController extends Controller {
    /*
     * Котроллер для создания слайдов в админке и управления ими
     */

    public function index(Slide $slide) {
        return $slide::all();
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'url' => 'sometimes',
            'type' => 'required',
            'active' => 'sometimes',
            'media_id' => 'sometimes',
            'tags' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($slide = Slide::create($fields)) {

            if (isset($fields['media_id'])) {
                $media = Media::query()->where('id', $fields['media_id'])->first();
                $slide->media()->syncWithPivotValues($media->id, ['type' => 1]);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($slide, $fields['tags']);
            }

            return $slide;
        }
    }

    public function update(Request $request, Slide $slide) {
        $fields = $request->validate([
            'name' => 'required',
            'url' => 'sometimes',
            'type' => 'required',
            'active' => 'sometimes',
            'media_id' => 'sometimes',
            'tags' => 'sometimes',
        ]);

        if (isset($fields['media_id'])) {
            $media = Media::query()->where('id', $fields['media_id'])->first();
            $slide->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($slide, $fields['tags']);
        }

        return $slide->update($fields);
    }

    public function edit(Slide $slide)
    {
        return SlideResource::make($slide);
    }
}
