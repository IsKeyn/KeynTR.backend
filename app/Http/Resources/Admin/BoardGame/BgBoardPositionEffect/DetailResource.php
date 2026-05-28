<?php

namespace App\Http\Resources\Admin\BoardGame\BgBoardPositionEffect;

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

            'description' => $this->description,
            'actions' => $this->actions,
        ];
    }
}
