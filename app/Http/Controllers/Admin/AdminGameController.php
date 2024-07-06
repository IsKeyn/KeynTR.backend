<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\GameResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GamingPlatformResource;
use App\Http\Resources\GenreResource;
use App\Models\Company;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Seo;
use App\Services\AdditionalFieldsService;
use App\Services\CompanyService;
use App\Services\GameService;
use App\Services\GenreService;
use App\Services\LinkService;
use App\Services\MediaService;
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
            'covers' => 'sometimes',
            'additional_fields' => 'sometimes',
            'genres' => 'sometimes',
            'companies' => 'sometimes',
            'tags' => 'sometimes',
            'seo' => 'sometimes',
            'links' => 'sometimes',
            'release_dates' => 'sometimes',
            'created_at' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($game = Game::create($fields)) {
            $mediaService = new MediaService();

            if (isset($fields['title_image'])) {
                $mediaService->setTitleImage($game, $fields['title_image']);
            }

            if (isset($fields['covers'])) {
                $mediaService->setCovers($game, $fields['covers']);
            }

            if (isset($fields['additional_fields'])) {
                $additionalFieldsService = new AdditionalFieldsService();
                $additionalFieldsService->sync($game, $fields['additional_fields']);
            }

            if (isset($fields['genres'])) {
                GenreService::set($game, $fields['genres']);
            }

            if (isset($fields['companies'])) {
                CompanyService::set($game, $fields['companies']);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($game, $fields['tags']);
            }

            if (isset($fields['seo']) && $fields['seo']) {
                if ($game->seo) {
                    $game->seo()->update($fields['seo']);
                } else {
                    $meta = new Seo($fields['seo']);
                    $game->seo()->save($meta);
                }
            }

            if (isset($fields['links'])) {
                LinkService::set($game, $fields['links']);
            }

            if (isset($fields['release_dates'])) {
                GameService::setReleaseDates($game, $fields['release_dates']);
            }

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
            'covers' => 'sometimes',
            'additional_fields' => 'sometimes',
            'genres' => 'sometimes',
            'companies' => 'sometimes',
            'tags' => 'sometimes',
            'seo' => 'sometimes',
            'links' => 'sometimes',
            'release_dates' => 'sometimes',
            'created_at' => 'sometimes',
        ]);

        $mediaService = new MediaService();

        if (isset($fields['title_image'])) {
            $mediaService->setTitleImage($game, $fields['title_image']);
        }

        if (isset($fields['covers'])) {
            $mediaService->setCovers($game, $fields['covers']);
        }

        if (isset($fields['additional_fields'])) {
            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($game, $fields['additional_fields']);
        }

        if (isset($fields['genres'])) {
            GenreService::set($game, $fields['genres']);
        }

        if (isset($fields['companies'])) {
            CompanyService::set($game, $fields['companies']);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($game, $fields['tags']);
        }

        if (isset($fields['seo']) && $fields['seo']) {
            if ($game->seo) {
                $game->seo()->update($fields['seo']);
            } else {
                $meta = new Seo($fields['seo']);
                $game->seo()->save($meta);
            }
        }

        if (isset($fields['release_dates'])) {
            GameService::setReleaseDates($game, $fields['release_dates']);
        }

        if (isset($fields['links'])) {
            LinkService::set($game, $fields['links']);
        }

        return $game->update($fields);
    }

    public function edit(Game $game)
    {
        return GameResource::make($game);
    }

    public function getAdditionalData() {
        return [
            'gaming_platform' => GamingPlatformResource::collection(GamingPlatform::all()),
            'genre' => GenreResource::collection(Genre::all()),
            'company' => CompanyResource::collection(Company::all()),
            'company_role' => GroupResource::collection(Group::where('entity_type', 'App\Models\Company')->get()),
        ];
    }
}
