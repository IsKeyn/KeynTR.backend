<?php

namespace App\Http\Resources\Admin\BoardGame\BgItem;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'actions' => $this->actions,
            'type' => $this->type,
            'drop_chance' => $this->drop_chance,
            'board_game_id' => $this->board_game_id,
            'author' => $this->author,
        ];
    }
}
