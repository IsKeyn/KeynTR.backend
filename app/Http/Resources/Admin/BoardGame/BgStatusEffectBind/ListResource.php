<?php

namespace App\Http\Resources\Admin\BoardGame\BgStatusEffectBind;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'status_effect_id' => $this->status_effect_id,
            'board_game_id' => $this->board_game_id,
        ];
    }
}
