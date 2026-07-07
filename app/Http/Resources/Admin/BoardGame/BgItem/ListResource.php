<?php

namespace App\Http\Resources\Admin\BoardGame\BgItem;

use App\Http\Resources\Media\ShortMediaResource;
use App\Models\Media;
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
            'price' => $this->price,
            'board_game_id' => $this->board_game_id,
            'author' => $this->author,

            'image' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->wherePivot('type', '=', Media::TITLE_TYPE)->first())),
            'sound' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->wherePivot('type', '=', Media::SOUND)->first())),
        ];
    }
}
