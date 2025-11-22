<?php

namespace App\Http\Resources\BoardGame\stats;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemStatsResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'additional_data' => $this->additional_data,
        ];
    }
}
