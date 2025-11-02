<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\Game\GameShortResource;
use App\Http\Resources\UserPublicResource;
use App\Services\BoardGame\GameService;
use Illuminate\Http\Resources\Json\JsonResource;

class GameListResource extends JsonResource
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
            'game_id' => $this->game_id,
            'game' => GameShortResource::make($this->game),
            'added_by_user' => UserPublicResource::make($this->addedBy),
            'gaming_platform_id' => $this->gaming_platform_id,
            'platform' => $this->platform,
            'board_game_id' => $this->board_game_id,
            'description' => $this->description,
            'points' => $this->points,
            'computed_points' => GameService::calcPoints($this),
            'rerolled_points' => GameService::rerollPoints($this->boardGame, $this),
            'coop' => $this->coop,
            'difficult' => $this->difficult,
            'game_completion_time' => $this->game_completion_time,
            'type' => $this->type,
            'active' => $this->active,
            'added_by' => $this->added_by,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
