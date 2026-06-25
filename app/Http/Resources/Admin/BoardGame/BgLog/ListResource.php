<?php

namespace App\Http\Resources\Admin\BoardGame\BgLog;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'bg_player_id' => $this->bg_player_id,
            'message' => $this->message,
            'board_game_id' => $this->board_game_id,
        ];
    }
}
