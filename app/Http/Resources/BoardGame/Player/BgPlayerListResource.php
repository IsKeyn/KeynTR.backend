<?php

namespace App\Http\Resources\BoardGame\Player;

use App\Http\Resources\BoardGame\Items\BgInventoryResource;
use App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameShortResource;
use App\Http\Resources\BoardGame\StatusEffects\BgPlayerStatusEffectResource;
use App\Http\Resources\User\UserPublicResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerListResource extends JsonResource
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
        $position = $this->whenLoaded('positions', function() {
            return $this->positions->first();
        });

        $fullPoints = $this->points;
        if ($position) $fullPoints += $position->position;

        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn() => UserPublicResource::make($this->user)),
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'full_points' => $fullPoints,
            'points_per_hour' => $this->points_per_hour,
            'streak' => $this->streak,
            'step_count' => $this->step_count,
            'item_roll_count' => $this->item_roll_count,
            'finishBoard' => $this->finishBoard,
            'position' => $position ? $position->position : null,
            'place' => $this->place,
            'timer' => $this->whenLoaded('mainTimers', fn() => $this->mainTimers->first()),
            'statusEffects' => $this->whenLoaded('statusEffects',
                fn() => BgPlayerStatusEffectResource::collection($this->statusEffects)
            ),
            'inventory' => $this->whenLoaded('inventory',
                fn() => BgInventoryResource::collection($this->inventory)
            ),
            'active' => $this->active,
            'not_active_reason' => $this->not_active_reason,
            'current_game' => $this->whenLoaded('currentGames', fn() => BgPlayerGameShortResource::make($this->currentGames->first())),
            'has_current_game' => $this->whenLoaded('currentGames', fn() => $this->currentGames->first() ?? false),
        ];
    }
}
