<?php

namespace App\Http\Resources\Game;

use App\Models\Game;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRollListResource extends JsonResource
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
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage)),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
            'release_dates' => $this->whenLoaded('dates', DateShortResource::collection($this->dates)),
        ];
    }
}
