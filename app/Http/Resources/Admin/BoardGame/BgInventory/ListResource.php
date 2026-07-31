<?php

namespace App\Http\Resources\Admin\BoardGame\BgInventory;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'board_game_item_id' => $this->board_game_item_id,
            'has_used' => $this->has_used,
            'use_result' => $this->use_result,
        ];
    }
}
