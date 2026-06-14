<?php

namespace App\Http\Resources\BoardGame\Games;

use App\Http\Resources\Game\GameShortResource;

use App\Http\Resources\GamingPlatform\GamingPlatformShortResource;
use App\Http\Resources\User\UserPublicResource;
use App\Services\BoardGame\GameService;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgGameResource extends JsonResource
{
    use CommonResourceFields;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'game_id' => $this->game_id,
            'board_game_id' => $this->board_game_id,
            'gaming_platform_id' => $this->gaming_platform_id,
            'points' => $this->points,
            'difficult' => $this->difficult,
            'game_completion_time' => $this->game_completion_time,
            'coop' => $this->coop,
            'list_type' => $this->list_type,
            'description' => $this->description,
            'source' => $this->source,
            'added_by' => $this->added_by,
            'game' => $this->whenLoaded('game', GameShortResource::make($this->game)),
            'platform' => $this->whenLoaded('platform', GamingPlatformShortResource::make($this->platform)),
            'added_by_user' => $this->whenLoaded('addedBy', UserPublicResource::make($this->addedBy)),
            'computed_points' => GameService::calcPoints($this),
            'rerollPenalty' => GameService::rerollPenalty($this->boardGame, $this),
        ];
    }
}
