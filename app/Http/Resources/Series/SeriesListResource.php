<?php

namespace App\Http\Resources\Series;

use App\Models\Game;
use App\Models\Media;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'entity_type' => Game::class,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
//            'covers' => $this->whenLoaded('media', ShortMediaResource::collection($this->media()->wherePivot('type', '=', Media::COVER_TYPE)->get())),
//            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
//            'release_dates' => $this->whenLoaded('dates', DateShortResource::collection($this->dates)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
