<?php

namespace App\Http\Resources\BoardGame;

use Illuminate\Http\Resources\Json\JsonResource;

class BoardPositionEffectsBindResource extends JsonResource // TODO устаревший ресурс, новый BgBoardPositionEffectsBindResource
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
            'position_effect_id' => $this->position_effect_id,
            'board_game_id' => $this->board_game_id,
            'position' => $this->position,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'boardPositionEffect' => BoardPositionEffectResource::make($this->boardPositionEffect),
        ];
    }
}
