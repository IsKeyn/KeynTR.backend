<?php

namespace App\Http\Resources\Game;

use App\Http\Resources\Date\DateWithPlatformResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class GameShortResource extends JsonResource
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
            ...$this->commonLoadedFields(),

            'release_dates' => $this->whenLoaded('dates', DateWithPlatformResource::collection($this->dates)),
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage)),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover)),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
        ];
    }
}
