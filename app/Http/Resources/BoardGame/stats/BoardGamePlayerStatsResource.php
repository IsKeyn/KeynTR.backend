<?php

namespace App\Http\Resources\BoardGame\stats;

use App\Http\Resources\UserPublicResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGamePlayerStatsResource extends JsonResource
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
            'user' => UserPublicResource::make($this->user),
            'additional_data' => $this->additional_data,
        ];
    }
}
