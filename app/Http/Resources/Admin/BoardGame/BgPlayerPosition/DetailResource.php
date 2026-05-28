<?php

namespace App\Http\Resources\Admin\BoardGame\BgPlayerPosition;

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

            'user_id' => $this->user_id,
            'position' => $this->position,
            'board_game_id' => $this->board_game_id,
            'has_use_effect' => $this->has_use_effect,
        ];
    }
}
