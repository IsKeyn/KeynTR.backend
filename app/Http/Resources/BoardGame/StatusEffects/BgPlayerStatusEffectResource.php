<?php

namespace App\Http\Resources\BoardGame\StatusEffects;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerStatusEffectResource extends JsonResource
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

            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'statusEffectBind' => $this->whenLoaded('statusEffectBind',
                fn() => BgPlayerStatusEffectBindResource::make($this->statusEffectBind)
            ),
            'status_effect_bind_id' => $this->status_effect_bind_id,
        ];
    }
}
