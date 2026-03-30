<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\BlockResource;

class AdminGameResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort' => $this->sort,
            'active' => $this->active,
            'show_in_list' => $this->show_in_list,

            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),

            'series' => $this->whenLoaded('series', AdminSeriesResource::collection($this->series)),
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

            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
