<?php

namespace App\Http\Resources\Admin\BoardGame\BgPlayerGame;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'user_id' => $this->user_id,
            'bg_player_id' => $this->bg_player_id,
            'board_game_game_list_id' => $this->board_game_game_list_id,
            'status' => $this->status,
            'board_game_id' => $this->board_game_id,
            'type' => $this->type,
            'from_user_id' => $this->from_user_id,
            'comment_id' => $this->comment_id,
            'time' => $this->time,
        ];
    }
}
