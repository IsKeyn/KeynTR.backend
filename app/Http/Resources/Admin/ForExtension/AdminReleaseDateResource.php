<?php

namespace App\Http\Resources\Admin\ForExtension;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminReleaseDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $gamePlatform = $this->gamePlatform()->first();

        return [
            'date' => $this->date,
            'gaming_platform' => $gamePlatform ? $gamePlatform->id : null,
            'addInfo' => $gamePlatform ? $gamePlatform->pivot->additional_info : null,
            'hideDay' => $this->hide_day,
            'hideMonth' => $this->hide_month,
        ];
    }
}
