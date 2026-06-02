<?php

namespace App\Http\Resources\Date;

use Illuminate\Http\Resources\Json\JsonResource;

class DateShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'hideDay' => $this->hide_day,
            'hideMonth' => $this->hide_month,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
