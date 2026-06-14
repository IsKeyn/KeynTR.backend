<?php

namespace App\Http\Resources\Admin\BoardGame\Timer;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'description' => $this->description,
            'limit' => $this->limit,
            'elapsed_seconds' => $this->elapsed_seconds,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
        ];
    }
}
