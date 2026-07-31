<?php

namespace App\Http\Resources\BoardGame;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerStatusEffectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    // TODO устаревший метод
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'statusEffect' => StatusEffectResource::make($this->statusEffect),
            'status_effect_id' => $this->status_effect_id,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
