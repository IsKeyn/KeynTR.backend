<?php

namespace App\Http\Resources\Admin\BoardGame\BgBoardPositionEffect;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'description' => $this->description,
            'actions' => $this->actions,
        ];
    }
}
