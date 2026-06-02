<?php

namespace App\Http\Resources\Admin\BoardGame\BgBoardPositionEffect;

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

            'title_image' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->wherePivot('type', '=', Media::TITLE_TYPE)->first())),
        ];
    }
}
