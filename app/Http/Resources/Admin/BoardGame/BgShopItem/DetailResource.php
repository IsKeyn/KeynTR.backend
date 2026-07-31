<?php

namespace App\Http\Resources\Admin\BoardGame\BgShopItem;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'bg_player_id' => $this->bg_player_id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'status' => $this->status,
            'bought_by_player_id' => $this->bought_by_player_id,
        ];
    }
}
