<?php

namespace App\Http\Resources\BoardGame\Board;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgBoardResource extends JsonResource
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

            'columns' => $this->columns,
            'media' => $this->media,
        ];
    }
}
