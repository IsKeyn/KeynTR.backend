<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
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
            'covers' => $this->whenLoaded('media', ShortMediaResource::collection($this->media()->wherePivot('type', '=', Media::COVER_TYPE)->get())),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
            'release_dates' => $this->whenLoaded('dates', DateShortResource::collection($this->dates)),
            'groups' => $this->whenLoaded('groups', GroupResource::collection($this->groups)),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
