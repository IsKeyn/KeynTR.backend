<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\UserPublicResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerInteractionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) // TODO устаревший ресурс, новый BgPlayerInteractionResource
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'board_game_id' => $this->board_game_id,
            'with_player' => $this->with_player,
            'with_player_data' => UserPublicResource::make($this->withPlayerData),
            'created_by' => $this->created_by,
            'created_by_data' => UserPublicResource::make($this->createdByData),
            'entity_id' => $this->entity_type,
            'active' => $this->active,
            'created_at' => $this->created_at,
        ];
    }
}
