<?php

namespace App\Http\Resources\Admin\BoardGame\BgGameList;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'game_id' => $this->game_id,
            'board_game_id' => $this->board_game_id,
            'gaming_platform_id' => $this->gaming_platform_id,
            'points' => $this->points,
            'difficult' => $this->difficult,
            'game_completion_time' => $this->game_completion_time,
            'coop' => $this->coop,
            'list_type' => $this->list_type,
            'description' => $this->description,
            'source' => $this->source,
            'added_by' => $this->added_by,
        ];
    }
}
