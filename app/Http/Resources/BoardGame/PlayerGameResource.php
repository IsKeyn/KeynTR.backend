<?php

namespace App\Http\Resources\BoardGame;


use App\Http\Resources\CommentResource;
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
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'game' => GameListResource::make($this->game),
            'board_game_game_list_id' => $this->board_game_game_list_id,
            'status' => $this->status,
            'board_game_id' => $this->board_game_id,
            'comment_id' => $this->comment_id,
            'comment' => CommentResource::make($this->comment),
            'time' => $this->time,
            'additional_data' => $this->additional_data,
            'timeSpend' => TimerService::timeInGame($this),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'other_players_actions' => PlayerGameService::actionsWithGame($this->board_game_game_list_id, $this->board_game_id),
        ];
    }
}
