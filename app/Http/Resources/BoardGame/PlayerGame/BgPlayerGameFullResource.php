<?php

namespace App\Http\Resources\BoardGame\PlayerGame;

use App\Http\Resources\BoardGame\GameListResource;
use App\Http\Resources\BoardGame\PlayerGameShortResource;
use App\Http\Resources\CommentResource;

use App\Http\Resources\User\UserPublicResource;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\TimerService;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgPlayerGameFullResource extends JsonResource
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
            'user' => $this->whenLoaded('user', UserPublicResource::make($this->user)),
            'board_game_game_list_id' => $this->board_game_game_list_id,

            // TODO расскомментировать и доделать
//            'game' => GameListResource::make($this->game),
//
//            'status' => $this->status,
//            'type' => $this->type,
//            'board_game_id' => $this->board_game_id,
//            'comment_id' => $this->comment_id,
//            'comment' => CommentResource::make($this->comment),
//            'time' => $this->time,
//            'timeSpend' => TimerService::timeInGame($this),
//            'rerollPenalty' => GameService::rerollPenalty($this->boardGame, $this),
//            'additional_data' => $this->additional_data,
//            'other_players_actions' => $otherPlayersActions ?  PlayerGameShortResource::collection($otherPlayersActions) : null,
//            'other_players_actions_in_other_events' => $otherPlayersActionsInOtherEvents ?  PlayerGameShortResource::collection($otherPlayersActionsInOtherEvents) : null,
        ];
    }
}
