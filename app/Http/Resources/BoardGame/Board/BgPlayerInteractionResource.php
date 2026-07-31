<?php

namespace App\Http\Resources\BoardGame\Board;

use App\Http\Resources\User\UserPublicResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerInteractionResource extends JsonResource
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

            'type' => $this->type,
            'status' => $this->status,
            'board_game_id' => $this->board_game_id,
            'with_player' => $this->with_player,
            'with_player_data' => $this->whenLoaded('withPlayerData', fn() => UserPublicResource::make($this->withPlayerData)),
            'created_by' => $this->created_by,
            'created_by_data' => $this->whenLoaded('createdByData', fn() => UserPublicResource::make($this->createdByData)),
            'entity_id' => $this->entity_type,
        ];
    }
}
