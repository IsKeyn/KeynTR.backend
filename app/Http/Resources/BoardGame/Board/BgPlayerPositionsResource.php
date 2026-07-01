<?php

namespace App\Http\Resources\BoardGame\Board;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerPositionsResource extends JsonResource
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

            'user_id' => $this->user_id,
            'position' => $this->position,
            'board_game_id' => $this->board_game_id,
            'bg_player_id' => $this->bg_player_id,
            'has_use_effect' => $this->has_use_effect,
        ];
    }
}
