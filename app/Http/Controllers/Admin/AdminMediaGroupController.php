<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MediaGroupResource;
use App\Models\MediaGroup;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMediaGroupController extends Controller {
    public function index(MediaGroup $mediaGroup)
    {
        return $mediaGroup::all();
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'slug' => Rule::unique('games', 'slug'),
            'description' => 'sometimes|string',
            'active' => 'sometimes',
            'media_group' => 'sometimes',
            'created_at' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($mediaGroup = MediaGroup::create($fields)) {
            $mediaService = new MediaService();

            if (isset($fields['media_group'])) {
                $mediaService->setMediaGroup($mediaGroup, $fields['media_group']);
            }

            return $mediaGroup;
        }
    }

    public function update(Request $request, MediaGroup $mediaGroup) {
        $fields = $request->validate([
            'name' => 'required|string',
            'slug' => Rule::unique('games', 'slug'),
            'description' => 'sometimes|string',
            'active' => 'sometimes',
            'media_group' => 'sometimes',
            'created_at' => 'sometimes',
        ]);

        $mediaService = new MediaService();

        if (isset($fields['media_group'])) {
            $mediaService->setMediaGroup($mediaGroup, $fields['media_group']);
        }

        return $mediaGroup->update($fields);
    }

    public function edit(MediaGroup $mediaGroup)
    {
        return MediaGroupResource::make($mediaGroup);
    }
}
