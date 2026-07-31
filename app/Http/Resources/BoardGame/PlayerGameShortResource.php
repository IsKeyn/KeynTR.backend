<?php

namespace App\Http\Resources\BoardGame;


use App\Http\Resources\CommentResource;
use App\Http\Resources\UserPublicResource;
use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerGameShortResource extends JsonResource // TODO устаревший
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
            'user' => UserPublicResource::make($this->user),
            'game' => GameListResource::make($this->game),
            'board_game_game_list_id' => $this->board_game_game_list_id,
            'status' => $this->status,
            'type' => $this->type,
            'board_game_id' => $this->board_game_id,
            'comment_id' => $this->comment_id,
            'comment' => CommentResource::make($this->comment),
            'time' => $this->time,
            'timeSpend' => TimerService::timeInGame($this),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
