<?php

namespace App\Http\Resources\GamingPlatform;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
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
            'entity_type' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
