<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\UserPublicResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\Timer;
use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGamePlayerWithInventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        $position = BoardGamePlayerPosition::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->orderBy('updated_at', 'desc')->orderBy('updated_at', 'desc')->first();

        $fullPoints = $this->points;

        if ($position) {
            $fullPoints += $position->position;
        }

        $inventory = BoardGameInventory::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->get();

        $timer = Timer::query()
            ->where('user_id', $this->user_id)
            ->where('board_game_id', $this->board_game_id)
            ->where('slug','main')
            ->where('active', true)
            ->orderBy('id', 'desc')->first();

        $status = TimerService::getTimerStatus($timer);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserPublicResource::make($this->user),
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'item_roll_count' => $this->item_roll_count,
            'position' => $position ? $position->position : '',
            'full_points' => $fullPoints,
            'seconds' => $status['time'],
            'active' => $this->active,
            'not_active_reason' => $this->not_active_reason,
            'inventory' => BoardGameInventoryResource::collection($inventory),
        ];
    }
}
