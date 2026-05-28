<?php

namespace App\Http\Resources\Admin\BoardGame\BgStatusEffect;

use App\Http\Resources\Media\ShortMediaResource;
use App\Models\Media;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'description' => $this->description,
            'actions' => $this->actions,
            'board_game_id' => $this->board_game_id,
            'debuff' => $this->debuff,

            'image' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->wherePivot('type', '=', Media::TITLE_TYPE)->first())),
        ];
    }
}
