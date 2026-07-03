<?php

namespace App\Http\Resources\Admin\BoardGame\Player;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'points_per_hour' => $this->points_per_hour,
            'place' => $this->place,
            'item_roll_count' => $this->item_roll_count,
            'step_count' => $this->step_count,
            'streak' => $this->streak,
            'active' => $this->active,
            'not_active_reason' => $this->not_active_reason,
            'settings' => $this->settings,
            'premium' => $this->premium,
            'sort' => $this->sort,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
