<?php

namespace App\Http\Resources\Admin\Movie;

use App\Http\Resources\Admin\ForExtension\AdminAnonsDateResource;
use App\Http\Resources\Admin\ForExtension\AdminCompanyResource;
use App\Http\Resources\Admin\ForExtension\AdminGenreResource;
use App\Http\Resources\Admin\ForExtension\AdminGroupResource;
use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\Admin\ForExtension\AdminPeopleResource;
use App\Http\Resources\Admin\ForExtension\AdminReleaseDateResource;
use App\Http\Resources\Admin\ForExtension\AdminSeriesResource;
use App\Http\Resources\BlockResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort' => $this->sort,
            'active' => $this->active,

            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),

            'series' => $this->whenLoaded('series', AdminSeriesResource::collection($this->series)),
            'people' => $this->whenLoaded('people', AdminPeopleResource::collection($this->people)),
            'groups' =>  $this->whenLoaded('groups', AdminGroupResource::collection($this->groups)),
            'genres' => $this->whenLoaded('genres', AdminGenreResource::collection($this->genres)),
            'anons_dates' => $this->whenLoaded('anonsDates', AdminAnonsDateResource::collection($this->anonsDates)),
            'release_dates' => $this->whenLoaded('dates', AdminReleaseDateResource::collection($this->dates)),

            'companies' => $this->whenLoaded('company', AdminCompanyResource::collection($this->company)),
            'links' => $this->whenLoaded('link', AdminLinkResource::collection($this->link)),

            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),

            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),
            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
