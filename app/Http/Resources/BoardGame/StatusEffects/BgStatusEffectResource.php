<?php

namespace App\Http\Resources\BoardGame\StatusEffects;

use App\Http\Resources\Media\ShortMediaResource;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgStatusEffectResource extends JsonResource
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

            'type' => $this->type,
            'description' => $this->description,
            'actions' => $this->actions,
            'board_game_id' => $this->board_game_id,
            'debuff' => $this->debuff,
            'image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage)),
        ];
    }
}
