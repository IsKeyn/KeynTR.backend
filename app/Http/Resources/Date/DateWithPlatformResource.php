<?php

namespace App\Http\Resources\Date;

use App\Http\Resources\GamingPlatform\GamingPlatformShortResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DateWithPlatformResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $gamePlatform = $this->whenLoaded('gamePlatform', function() {
            return $this->gamePlatform()->first();
        });

        return [
            'id' => $this->id,
            'date' => $this->date,
            'hideDay' => $this->hide_day,
            'hideMonth' => $this->hide_month,
            'game_platform' => $gamePlatform ? GamingPlatformShortResource::make($gamePlatform) : null,
            'addInfo' => $gamePlatform && isset($gamePlatform->pivot) ? $gamePlatform->pivot->additional_info : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
