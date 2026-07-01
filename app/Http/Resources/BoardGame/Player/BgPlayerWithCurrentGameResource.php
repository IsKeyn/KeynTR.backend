<?php

namespace App\Http\Resources\BoardGame\Player;

use App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameShortResource;
use App\Http\Resources\User\UserPublicResource;
use App\Services\BoardGame\TimerService;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerWithCurrentGameResource extends JsonResource
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
        if ($position && $position->position) $fullPoints += $position->position;

        $status = null;

        $timer = $this->whenLoaded('mainTimers', fn() => $this->mainTimers->first());
        if ($timer) {
            $status = TimerService::getTimerStatus($timer);
        }

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
            'timer_status' => $status,
            'finishBoard' => $this->finishBoard,
            'position' => $position ? $position->position : null,
            'place' => $this->place,
            'not_active_reason' => $this->not_active_reason,
            'current_game' => $this->whenLoaded('currentGames', fn() => BgPlayerGameShortResource::make($this->currentGames->first())),
            'has_current_game' => $this->whenLoaded('currentGames', fn() => $this->currentGames->isNotEmpty()),
        ];
    }
}
