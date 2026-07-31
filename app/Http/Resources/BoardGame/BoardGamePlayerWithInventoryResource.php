<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\UserPublicResource;
use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGamePlayerWithInventoryResource extends JsonResource // TODO устаревший метод BgPlayerWithInventoryResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        $position = $this->whenLoaded('positions', $this->positions->where('board_game_id', $this->board_game_id)->sortByDesc('id')->first());

        $fullPoints = $this->points;
        if ($position) $fullPoints += $position->position;

        $timer = $this->whenLoaded('mainTimers', $this->mainTimers->where('board_game_id', $this->board_game_id)->first());
        $status = TimerService::getTimerStatus($timer);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', UserPublicResource::make($this->user)),
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'full_points' => $fullPoints,
            'item_roll_count' => $this->item_roll_count,
            'inventory' => $this->whenLoaded('inventory', BoardGameInventoryResource::collection($this->inventory->where('board_game_id', $this->board_game_id)->sortByDesc('created_at'))),
            'status_effects' => $this->whenLoaded('statusEffects', PlayerStatusEffectResource::collection($this->statusEffects->where('board_game_id', $this->board_game_id)->sortByDesc('created_at'))),
            'position' => $position->position ?? null,
            'seconds' => $status['time'] ?? null,
            'active' => $this->active,
            'not_active_reason' => $this->not_active_reason,
        ];
    }
}
