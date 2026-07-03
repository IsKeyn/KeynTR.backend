<?php

namespace App\Http\Resources\BoardGame\PlayerGame;

use App\Http\Resources\BoardGame\BgShortResource;
use App\Http\Resources\BoardGame\Games\BgGameResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\User\UserPublicResource;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\TimerService;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerGameWithPointsShortResource extends JsonResource
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

            'user_id' => $this->user_id,
            'bg_player_id' => $this->bg_player_id,
            'board_game_id' => $this->board_game_id,
            'board_game_game_list_id' => $this->board_game_game_list_id,
            'status' => $this->status,
            'type' => $this->type,
            'user' => $this->whenLoaded('user', fn() => UserPublicResource::make($this->user)),
            'game' => $this->whenLoaded('game', fn() => BgGameResource::make($this->game)),
            'board_game' => $this->whenLoaded('boardGame', fn() => BgShortResource::make($this->boardGame)),
            'comment_id' => $this->comment_id,
            'comment' => $this->whenLoaded('comment', fn() => CommentResource::make($this->comment)),
            'time' => $this->time,
            'points' => $this->points,
            'computed_points' => $this->whenLoaded('game', fn() => GameService::calcPoints($this->game)),
            'points_for_finish' => $this->whenLoaded('player', fn() => GameService::finishPoints($this->player, $this)),
            'rerollPenalty' => GameService::rerollPenalty($this->boardGame, $this),
            'timeSpend' => TimerService::timeInGame($this),
            'finished_at' => $this->finished_at,
        ];
    }
}
