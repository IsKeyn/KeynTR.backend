<?php

namespace App\Http\Resources\BoardGame\Board\Cell;

use App\Http\Resources\BoardGame\BgShortResource;
use App\Http\Resources\Comments\CommentResource;
use App\Http\Resources\User\UserPublicResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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

            'player_id' => $this->player_id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn() => UserPublicResource::make($this->user)),
            'board_game_id' => $this->board_game_id,
            'board_game' => $this->whenLoaded('boardGame', fn() => BgShortResource::make($this->boardGame)),
            'board_position_effects_id' => $this->board_position_effects_id,
            'comment_id' => $this->comment_id,
            'time' => $this->completion_time_seconds,
            'comment' => $this->whenLoaded('comment', fn() => CommentResource::make($this->comment)),
        ];
    }
}
