<?php

namespace App\Http\Resources\BoardGame;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerInteractionResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'board_game_id' => $this->board_game_id,
            'with_player' => $this->with_player,
            'with_player_data' => $this->withPlayerData,
            'created_by' => $this->created_by,
            'entity_id' => $this->entity_type,
            'active' => $this->active,
        ];
    }
}
