<?php

namespace App\Http\Resources\BoardGame\stats;

use Illuminate\Http\Resources\Json\JsonResource;

class GameListStatsResource extends JsonResource
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
            'game' => GameStatsResource::make($this->game),
        ];
    }
}
