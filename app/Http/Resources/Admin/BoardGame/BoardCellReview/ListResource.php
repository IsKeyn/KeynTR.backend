<?php

namespace App\Http\Resources\Admin\BoardGame\BoardCellReview;

use App\Http\Resources\Comments\CommentResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'player_id' => $this->player_id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'board_position_effects_id' => $this->board_position_effects_id,
            'comment_id' => $this->comment_id,
            'completion_time_seconds' => $this->completion_time_seconds,
        ];
    }
}
