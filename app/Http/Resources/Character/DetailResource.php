<?php

namespace App\Http\Resources\Character;

use App\Http\Resources\Game\GameListResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
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

            'entity_type' => $this->model,
            'show_in_list' => $this->show_in_list,

            'title_image' => $this->whenLoaded('titleImage', fn() => ShortMediaResource::make($this->titleImage)),
            'covers' => $this->whenLoaded('cover', fn() =>  ShortMediaResource::collection($this->cover)),
            'games' => $this->whenLoaded('games', fn() =>  GameListResource::collection($this->games)),
        ];
    }
}
