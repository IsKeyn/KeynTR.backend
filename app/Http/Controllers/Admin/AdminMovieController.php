<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MovieResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GenreResource;
use App\Models\Company;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Movie;
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

class AdminMovieController extends Controller {
    public function index(Movie $movie)
    {
        return $movie::all();
    }

    public function store(Request $request)
    {
        $validated = $this->validateFields($request);

        $validated['created_by'] = isset($validated['created_by']) ? $validated['created_by'] : $request->user()->id;
        $validated['active'] = true;

        if (!$validated['created_at']) {
            unset($validated['created_at']);
        }

        if ($movie = Movie::create($validated)) {
            $this->setAdditionalFields($movie, $validated);
            return $movie;
        }
    }

    public function update(Request $request, Movie $movie) {
        $validated = $this->validateFields($request);

        $this->setAdditionalFields($movie, $validated);

        return $movie->update($validated);
    }

    public function edit(Movie $movie)
    {
        return MovieResource::make($movie);
    }

    public function validateFields($request) {
        return $request->validate([
            'name' => 'required|string',
            'slug' => Rule::unique('games', 'slug')->ignore($request->get('id')), // Может сломать store
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
            'created_at' => 'nullable',
        ]);
    }

    public function setAdditionalFields($model, $validated) {
        $mediaService = new MediaService();

        if (isset($validated['title_image'])) {
            $mediaService->setTitleImage($model, $validated['title_image']);
        }

        if (isset($validated['covers'])) {
            $mediaService->setCovers($model, $validated['covers']);
        }

        if (isset($validated['additional_fields'])) {
            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($model, $validated['additional_fields']);
        }

        if (isset($validated['genres'])) {
            GenreService::set($model, $validated['genres']);
        }

        if (isset($validated['companies'])) {
            CompanyService::set($model, $validated['companies']);
        }

        if (isset($validated['tags'])) {
            TagService::attacheTagsToEntity($model, $validated['tags']);
        }

        if (isset($validated['seo']) && $validated['seo']) {
            if ($model->seo) {
                $model->seo()->update($validated['seo']);
            } else {
                $meta = new Seo($validated['seo']);
                $model->seo()->save($meta);
            }
        }

//        if (isset($validated['release_dates'])) {
//            GameService::setReleaseDates($model, $validated['release_dates']);
//        }

        if (isset($validated['links'])) {
            LinkService::set($model, $validated['links']);
        }
    }

    public function getAdditionalData() {
        return [
            'genre' => GenreResource::collection(Genre::all()),
            'company' => CompanyResource::collection(Company::all()),
            'company_role' => GroupResource::collection(Group::where('entity_type', 'App\Models\Company')->get()),
        ];
    }
}
