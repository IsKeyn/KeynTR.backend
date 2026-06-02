<?php

namespace App\Http\Resources\Admin\BoardGame\BgItemBind;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'item_id' => $this->item_id,
            'board_game_id' => $this->board_game_id,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
