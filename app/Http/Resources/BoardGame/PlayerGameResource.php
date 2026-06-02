<?php

namespace App\Http\Resources\BoardGame;


use App\Http\Resources\CommentResource;
use App\Http\Resources\UserPublicResource;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\TimerService;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // TODO Не оптимизирвоанный легаси, перевести на App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameFullResource

        $otherPlayersActions = PlayerGameService::actionsWithGame($this->board_game_game_list_id, $this->board_game_id);
        $otherPlayersActionsInOtherEvents = PlayerGameService::actionsWithGameInOtherEvents($this->game, $this->board_game_id);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserPublicResource::make($this->user),
            'board_game_game_list_id' => $this->board_game_game_list_id,
            'game' => GameListResource::make($this->game),
            'status' => $this->status,
            'type' => $this->type,
            'board_game_id' => $this->board_game_id,
            'comment_id' => $this->comment_id,
            'comment' => CommentResource::make($this->comment),
            'time' => $this->time,
            'timeSpend' => TimerService::timeInGame($this),
            'rerollPenalty' => GameService::rerollPenalty($this->boardGame, $this),
            'additional_data' => $this->additional_data,
            'other_players_actions' => $otherPlayersActions ?  PlayerGameShortResource::collection($otherPlayersActions) : null,
            'other_players_actions_in_other_events' => $otherPlayersActionsInOtherEvents ?  PlayerGameShortResource::collection($otherPlayersActionsInOtherEvents) : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
