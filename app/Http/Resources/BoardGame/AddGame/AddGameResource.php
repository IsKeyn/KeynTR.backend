<?php

namespace App\Http\Resources\BoardGame\AddGame;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class AddGameResource extends JsonResource
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

            'bg_player_id' => $this->bg_player_id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'gaming_platform_id' => $this->gaming_platform_id,
            'coop' => $this->coop,
            'game_completion_time' => $this->game_completion_time,
            'difficulty' => $this->difficulty,
            'description' => $this->description,
            'comment_for_moderator' => $this->comment_for_moderator,
            'moderator_comment' => $this->moderator_comment,
            'status' => $this->status,
        ];
    }
}
