<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\UserLightResource;
use App\Models\User;
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
            'user' => UserLightResource::make($this->user),
        ];
    }
}
