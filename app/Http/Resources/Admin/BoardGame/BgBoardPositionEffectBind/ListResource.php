<?php

namespace App\Http\Resources\Admin\BoardGame\BgBoardPositionEffectBind;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'position_effect_id' => $this->position_effect_id,
            'board_game_id' => $this->board_game_id,
            'position' => $this->position,
            'active' => $this->active,
        ];
    }
}
