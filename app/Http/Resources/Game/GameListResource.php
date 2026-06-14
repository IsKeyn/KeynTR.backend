<?php

namespace App\Http\Resources\Game;

use App\Http\Resources\GroupResource;
use App\Models\Game;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class GameListResource extends JsonResource
{
    use CommonResourceFields;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'entity_type' => Game::class,
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover)),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
            'release_dates' => $this->whenLoaded('dates', DateShortResource::collection($this->dates)),
            'groups' => $this->whenLoaded('groups', GroupResource::collection($this->groups)),
        ];
    }
}
