<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\UserPublicResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGamePlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::where('id', $this->user_id)->first();
        $position = BoardGamePlayerPosition::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->orderBy('updated_at', 'desc')->first();
        $inventory = BoardGameInventory::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->get();
        $playerStatusEffect = PlayerStatusEffect::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->get();

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
            'inventory' => BoardGameInventoryResource::collection($inventory),
            'status_effects' => PlayerStatusEffectResource::collection($playerStatusEffect),
            'user' => UserPublicResource::make($user),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
