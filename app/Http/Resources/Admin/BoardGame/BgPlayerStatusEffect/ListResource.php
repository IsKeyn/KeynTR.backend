<?php

namespace App\Http\Resources\Admin\BoardGame\BgPlayerStatusEffect;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'user_id' => $this->user_id, // TODO se_refactoring устаревшее
            'bg_player_id' => $this->board_game_player_id,
            'board_game_id' => $this->board_game_id, // TODO se_refactoring устаревшее
            'status_effect_id' => $this->status_effect_id, // TODO se_refactoring устаревшее
            'status_effect_bind_id' => $this->status_effect_bind_id,
        ];
    }
}
