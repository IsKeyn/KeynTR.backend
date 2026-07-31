<?php

namespace App\Http\Resources\Admin\BoardGame\BgStatusEffect;

use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'type' => $this->type,
            'description' => $this->description,
            'actions' => $this->actions,
            'board_game_id' => $this->board_game_id,
            'debuff' => $this->debuff,

            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage)),
        ];
    }
}
