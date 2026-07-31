<?php

namespace App\Http\Resources\Comments;

use App\Http\Resources\BoardGame\BoardGameShortestResource;
use App\Http\Resources\User\UserWithAvatarResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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

            'message' => $this->message,
            'answers' => $this->answers ? CommentResource::collection($this->answers) : null,
            'user' => $this->whenLoaded('user', UserWithAvatarResource::make($this->user)),
            'board_game' => $this->getBoardGameWhenLoaded(),
        ];
    }

    protected function getBoardGameWhenLoaded()
    {
        // Проверяем загружено ли первое отношение
        if (!$this->relationLoaded('bgPlayerGame')) {
            return null;
        }

        // Проверяем существует ли bgPlayerGame
        if (!$this->bgPlayerGame) {
            return null;
        }

        // Проверяем загружено ли второе отношение
        if (!$this->bgPlayerGame->relationLoaded('boardGame')) {
            return null;
        }

        // Проверяем существует ли boardGame
        if (!$this->bgPlayerGame->boardGame) {
            return null;
        }

        return new BoardGameShortestResource($this->bgPlayerGame->boardGame);
    }
}
