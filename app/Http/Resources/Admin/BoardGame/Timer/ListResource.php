<?php

namespace App\Http\Resources\Admin\BoardGame\Timer;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'limit' => $this->limit,
            'active' => $this->active,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
