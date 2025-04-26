<?php

namespace App\Http\Resources\BoardGame;

use Illuminate\Http\Resources\Json\JsonResource;

class BoardGameInventoryResource extends JsonResource
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
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'board_game_item_id' => $this->board_game_item_id,
            'item' => BoardGameItemResource::make($this->item),
            'has_used' => $this->has_used,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
