<?php

namespace App\Http\Resources\BoardGame;

use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class TimerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'limit' => $this->limit,
            'settings' => $this->settings,
            'status' => TimerService::getTimerStatus($this),
            'active' => $this->actions,
            'board_game_id' => $this->board_game_id,
            'user_id' => $this->user_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
