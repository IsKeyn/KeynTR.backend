<?php

namespace App\Http\Resources\Event;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class StreamersOnlineResource extends JsonResource
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
            'site_user_id' => $this['site_user_id'],
            'user_name' => $this['user_name'],
            'board_games_list' => BoardGameListResource::collection($this['board_games_list']),
        ];
    }
}
