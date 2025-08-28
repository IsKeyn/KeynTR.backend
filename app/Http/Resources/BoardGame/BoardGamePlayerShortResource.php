<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\UserPublicResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\Timer;
use App\Models\User;
use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGamePlayerShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $position = BoardGamePlayerPosition::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->orderBy('updated_at', 'desc')->first();

        $fullPoints = $this->points;

        if ($position) {
            $fullPoints += $position->position;
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'position' => $position ? $position->position : '',
            'full_points' => $fullPoints,
        ];
    }
}
