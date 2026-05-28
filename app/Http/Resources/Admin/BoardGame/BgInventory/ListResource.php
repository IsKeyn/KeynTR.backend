<?php

namespace App\Http\Resources\Admin\BoardGame\BgInventory;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'board_game_item_id' => $this->board_game_item_id,
            'has_used' => $this->has_used,
            'use_result' => $this->use_result,
            'sort' => $this->sort,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
