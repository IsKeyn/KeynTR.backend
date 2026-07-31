<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\User\UserPublicResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
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
            'message' => $this->message,
            'board_game_id' => $this->board_game_id,
            'user' => $this->whenLoaded('user', fn() => UserPublicResource::make($this->user)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
