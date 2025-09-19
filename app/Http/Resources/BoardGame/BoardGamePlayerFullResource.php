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

class BoardGamePlayerFullResource extends JsonResource
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
        $inventory = BoardGameInventory::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->orderBy('updated_at', 'desc')->get();
        $playerStatusEffect = PlayerStatusEffect::where('board_game_id', $this->board_game_id)->where('user_id', $this->user_id)->get();
        $playerCurrentGame = PlayerGame::where('board_game_id', $this->board_game_id)
            ->where('user_id', $this->user_id)
            ->where('status', PlayerGame::CURRENT)->first();
        $playerGames = PlayerGame::where('board_game_id', $this->board_game_id)
            ->where('user_id', $this->user_id)->orderByDesc('id')->get();

        $fullPoints = $this->points;

        if ($position) {
            $fullPoints += $position->position;
        }

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
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'item_roll_count' => $this->item_roll_count,
            'position' => $position ? $position->position : '',
            'full_points' => $fullPoints,
            'inventory' => BoardGameInventoryResource::collection($inventory),
            'current_game' => PlayerGameResource::make($playerCurrentGame),
            'player_games' => PlayerGameResource::collection($playerGames),
            'status_effects' => PlayerStatusEffectResource::collection($playerStatusEffect),
            'seconds' => $status['time'],
            'active' => $this->active,
            'not_active_reason' => $this->not_active_reason,
            'user' => UserPublicResource::make($user),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
