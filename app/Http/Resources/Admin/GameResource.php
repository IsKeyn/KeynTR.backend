<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\MediaResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $image = $this->titleImage()->wherePivot('type', '=', Media::TITLE_TYPE)->first();
        $covers = $this->media()->wherePivot('type', '=', Media::COVER_TYPE)->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'platforms' => $this->platforms,
            'description' => $this->description,
            'release_dates' => ReleaseDateResource::collection($this->dates),
            'anons_dates' => AnonsDateResource::collection($this->anonsDates),
            'title_image' => $image ? MediaResource::make($image) : null,
            'covers' => $covers ? MediaResource::collection($covers) : null,
            'tags' => TagResource::collection($this->tags),
            'groups' => GroupResource::collection($this->groups),
            'genres' => GenreResource::collection($this->genres),
            'companies' => CompanyResource::collection($this->company),
            'links' => LinkResource::collection($this->link),
            'additional_fields' => $this->additionalFields,
            'seo' => $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null,
            'menu' => MenuTypeResource::collection($this->menu),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
