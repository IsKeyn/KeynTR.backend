<?php

namespace App\Http\Resources\Game;

use App\Http\Resources\Media\ShortMediaResource;
use App\Models\Game;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class GameShortestResource extends JsonResource
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
            'show_in_list' => $this->show_in_list,
            'title_image' => $this->whenLoaded('titleImage', fn() => ShortMediaResource::make($this->titleImage)),
        ];
    }
}
