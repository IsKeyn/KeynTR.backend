<?php

namespace App\Http\Resources\BoardGame\Games;

use App\Http\Resources\Game\GameShortestResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRouletteListResource extends JsonResource
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
            'game_id' => $this->game_id,
            'game' => $this->whenLoaded('game', GameShortestResource::make($this->game)),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
