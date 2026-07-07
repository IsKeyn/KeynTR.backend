<?php

namespace App\Http\Resources\BoardGame\Shop;

use App\Http\Resources\BoardGame\Items\BgItemBindResource;
use App\Http\Resources\User\UserPublicResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgShopItemItemResource extends JsonResource
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
            ...$this->commonLoadedFields(),

            'bg_player_id' => $this->bg_player_id,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'status' => $this->status,
            'bought_by_player_id' => $this->bought_by_player_id,
            'item' => $this->whenLoaded('entity', fn() => BgItemBindResource::make($this->entity)),
            'user' => $this->whenLoaded('user', fn() => UserPublicResource::make($this->user)),
        ];
    }
}
