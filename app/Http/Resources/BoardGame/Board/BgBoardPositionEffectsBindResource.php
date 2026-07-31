<?php

namespace App\Http\Resources\BoardGame\Board;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgBoardPositionEffectsBindResource extends JsonResource
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

            'position_effect_id' => $this->position_effect_id,
            'board_game_id' => $this->board_game_id,
            'position' => $this->position,
            'boardPositionEffect' => BgBoardPositionEffectResource::make($this->boardPositionEffect),
        ];
    }
}
