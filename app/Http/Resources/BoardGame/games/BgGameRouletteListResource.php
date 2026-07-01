<?php

namespace App\Http\Resources\BoardGame\Games;

use App\Http\Resources\Game\GameShortestResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgGameRouletteListResource extends JsonResource
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

            'game_id' => $this->game_id,
            'game' => $this->whenLoaded('game', fn() => GameShortestResource::make($this->game)),
        ];
    }
}
