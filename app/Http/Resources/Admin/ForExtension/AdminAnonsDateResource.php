<?php

namespace App\Http\Resources\Admin\ForExtension;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminAnonsDateResource extends JsonResource
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
            'date' => $this->date,
            'hideDay' => $this->hide_day,
            'hideMonth' => $this->hide_month,
        ];
    }
}
