<?php

namespace App\Http\Resources\BoardGame\StatusEffects;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerStatusEffectBindResource extends JsonResource
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

            'status_effect_id' => $this->status_effect_id,
            'board_game_id' => $this->board_game_id,
            'statusEffect' => $this->whenLoaded('statusEffect',
                fn() => BgStatusEffectResource::make($this->statusEffect)
            ),
        ];
    }
}
