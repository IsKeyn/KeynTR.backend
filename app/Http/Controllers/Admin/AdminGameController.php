<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\GameResource;
use App\Http\Resources\GamingPlatformResource;
use App\Models\Date;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Media;
use App\Models\Slide;
use App\Services\DateService;
use App\Services\GameService;
use App\Services\TagService;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminGameController extends Controller {
    /*
     * Котроллер для создания слайдов в админке и управления ими
     */

    public function index(Game $game)
    {
        return $game::all();
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'slug' => Rule::unique('games', 'slug'),
            'description' => 'sometimes|string',
            'active' => 'sometimes',
            'title_image' => 'sometimes',
            'tags' => 'sometimes',
            'release_dates' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($game = Game::create($fields)) {
            if (isset($fields['title_image'])) {
                $media = Media::query()->where('id', $fields['title_image'])->first();
                $game->media()->syncWithPivotValues($media->id, ['type' => 1], false);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($game, $fields['tags']);
            }

            GameService::setReleaseDates($game, $fields['release_dates']);

            return $game;
        }
    }

    public function update(Request $request, Game $game) {
        $fields = $request->validate([
            'name' => 'required|string',
            'slug' => Rule::unique('games', 'slug')->ignore($request->get('id')),
            'description' => 'sometimes|string',
            'active' => 'sometimes',
            'title_image' => 'sometimes',
            'tags' => 'sometimes',
            'release_dates' => 'sometimes',
        ]);

        if (isset($fields['title_image'])) {
            $media = Media::query()->where('id', $fields['title_image'])->first();
            $game->media()->syncWithPivotValues($media->id, ['type' => 1], false);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($game, $fields['tags']);
        }

        GameService::setReleaseDates($game, $fields['release_dates']);

        return $game->update($fields);
    }

    public function edit(Game $game)
    {
        return GameResource::make($game);
    }

    public function getAdditionalData() {
        return [
            'gaming_platform' => GamingPlatformResource::collection(GamingPlatform::all()),
        ];
    }
}
