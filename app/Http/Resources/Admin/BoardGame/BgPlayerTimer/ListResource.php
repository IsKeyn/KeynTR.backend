<?php

namespace App\Http\Resources\Admin\BoardGame\BgPlayerTimer;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'timer_id' => $this->timer_id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'time_start' => $this->time_start,
            'time_stop' => $this->time_stop,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
