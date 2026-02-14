<?php

namespace App\Http\Resources\Menu;

use App\Http\Resources\MenuElementsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuTypeResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'group' => $this->group,
            'elements' => $this->whenLoaded('elements', MenuElementsResource::collection($this->elements)),
        ];
    }
}
