<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\Admin\GameResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GameListResource extends JsonResource
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
            'game' => GameResource::make($this->game),
            'gaming_platform_id' => $this->gaming_platform_id,
            'platform' => $this->platform,
            'board_game_id' => $this->board_game_id,
            'description' => $this->description,
            'points' => $this->points,
            'active' => $this->active,
            'added_by' => $this->added_by,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
