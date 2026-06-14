<?php

namespace App\Http\Resources\BoardGame;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemBindResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) // TODO устаревший ресурс
    {
        return [
            'id' => $this->id,
            'board_game_id' => $this->board_game_id,
            'active' => $this->active,
            'item' => ItemResource::make($this->item),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
