<?php

namespace App\Http\Resources\BoardGame;


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
            'time' => $this->time,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
