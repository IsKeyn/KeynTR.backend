<?php

namespace App\Http\Resources\Admin\BoardGame\Timer;

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

            'description' => $this->description,
            'limit' => $this->limit,
            'settings' => $this->settings,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
        ];
    }
}
