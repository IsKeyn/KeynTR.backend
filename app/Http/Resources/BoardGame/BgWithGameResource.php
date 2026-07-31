<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\BoardGame\Games\BgGameResource;
use App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameShortResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\SettingResource;
use App\Services\BoardGame\PlayerGameService;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgWithGameResource extends JsonResource
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
        $otherPlayersActions = PlayerGameService::actionsWithGame($this->board_game_game_list_id, $this->board_game_id);
        $otherPlayersActionsInOtherEvents = PlayerGameService::actionsWithGameInOtherEvents($this->game, $this->board_game_id);

        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'entity_type' => $this->model ?? null,
            'is_close' => $this->is_close,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,

            'settings' => $this->whenLoaded('settings', fn() => SettingResource::collection($this->settings)),
            'media' => $this->whenLoaded('media', fn() => ShortMediaResource::make($this->media->first())),

            'game' => $this->whenLoaded('games', fn() => BgGameResource::make($this->games->first())),

            'other_players_actions' => $otherPlayersActions ?? BgPlayerGameShortResource::collection($otherPlayersActions),
            'other_players_actions_in_other_events' => $otherPlayersActionsInOtherEvents ?? BgPlayerGameShortResource::collection($otherPlayersActionsInOtherEvents),
        ];
    }
}
