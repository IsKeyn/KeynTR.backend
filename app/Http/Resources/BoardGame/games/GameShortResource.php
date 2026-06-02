<?php

namespace App\Http\Resources\BoardGame\games;

use App\Http\Resources\GenreResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ReleaseDateResource;
use App\Models\Game;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class GameShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $covers = $this->media()->wherePivot('type', '=', Media::COVER_TYPE)->get();

        return [
            'id' => $this->id,
            'entity_type' => Game::class,
            'name' => $this->name,
            'slug' => $this->slug,
            'platforms' => $this->gamePlatform,
            'description' => $this->description,
            'active' => $this->active,
            'release_dates' => ReleaseDateResource::collection($this->dates),
            'covers' => $covers ? MediaResource::collection($covers) : null,
            'genres' => GenreResource::collection($this->genres),
            'additional_fields' => $this->additionalFields,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
