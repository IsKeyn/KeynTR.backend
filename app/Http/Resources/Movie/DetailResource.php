<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\BlockResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Company\CompanyWithGroupResource;
use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\Date\DateWithPlatformResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\Person\ListResource;
use App\Http\Resources\Series\SeriesWithGamesResource;

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

            'series' => $this->whenLoaded('series', SeriesWithGamesResource::collection($this->series)),
            'people' => $this->whenLoaded('people', ListResource::collection($this->people)),
            'groups' => $this->whenLoaded('groups', GroupResource::collection($this->groups)),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
            'anons_dates' => $this->whenLoaded('anonsDates', DateShortResource::collection($this->anonsDates)),
            'release_dates' => $this->whenLoaded('dates', DateWithPlatformResource::collection($this->dates)),

            'companies' => $this->whenLoaded('company', CompanyWithGroupResource::collection($this->company)),
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
