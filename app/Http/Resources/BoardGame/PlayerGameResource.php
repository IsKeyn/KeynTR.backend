<?php

namespace App\Http\Resources\BoardGame;


use App\Http\Resources\CommentResource;
use App\Http\Resources\UserPublicResource;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $otherPlayersActions = PlayerGameService::actionsWithGame($this->board_game_game_list_id, $this->board_game_id);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserPublicResource::make($this->user),
            'board_game_game_list_id' => $this->board_game_game_list_id,
            'game' => GameListResource::make($this->game),
            'status' => $this->status,
            'type' => $this->type,
            'board_game_id' => $this->board_game_id,
            'comment_id' => $this->comment_id,
            'comment' => CommentResource::make($this->comment),
            'time' => $this->time,
            'timeSpend' => TimerService::timeInGame($this),
            'rerolled_points' => GameService::rerollPoints($this->boardGame, $this),
            'additional_data' => $this->additional_data,
            'other_players_actions' => $otherPlayersActions ?  PlayerGameShortResource::collection($otherPlayersActions) : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
