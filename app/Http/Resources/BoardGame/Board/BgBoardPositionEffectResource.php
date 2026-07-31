<?php

namespace App\Http\Resources\BoardGame\Board;

use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgBoardPositionEffectResource extends JsonResource
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

            'actions' => $this->actions,
            'title_image' => $this->whenLoaded('titleImage', fn() => ShortMediaResource::make($this->titleImage)),
        ];
    }
}
