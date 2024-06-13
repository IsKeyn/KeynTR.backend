<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ReleaseDateResource extends JsonResource
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
        ];
    }
}
