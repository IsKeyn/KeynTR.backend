<?php

namespace App\Http\Resources\Admin\BoardGame\BgLog;

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

            'message' => $this->message,
            'board_game_id' => $this->board_game_id,
        ];
    }
}
