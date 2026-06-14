<?php

namespace App\Http\Resources\BoardGame\Items;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgInventoryResource extends JsonResource
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

            'bg_player_id' => $this->bg_player_id,
            'board_game_item_id' => $this->board_game_item_id,
            'item' => $this->whenLoaded('item', BgItemBindResource::make($this->item)),
            'has_used' => $this->has_used,
        ];
    }
}
