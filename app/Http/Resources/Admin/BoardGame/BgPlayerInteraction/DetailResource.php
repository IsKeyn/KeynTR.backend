<?php

namespace App\Http\Resources\Admin\BoardGame\BgPlayerInteraction;

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

            'type' => $this->type,
            'status' => $this->status,
            'board_game_id' => $this->board_game_id,
            'bg_player_id' => $this->bg_player_id,
            'with_player' => $this->with_player,
            'created_by' => $this->created_by,
            'entity_id' => $this->entity_id,
            'entity_type' => $this->entity_type,
        ];
    }
}
