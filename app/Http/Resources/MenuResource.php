<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'group_icon' => $this->group_icon,
            'menu_type_bind_id' => $this->menu_type_bind_id,
            'menu_type_bind_type' => $this->menu_type_bind_type,
            'sort' => $this->sort,
            'active' => $this->active,
            'elements' => $this->whenLoaded('elements', MenuElementsResource::collection($this->elements->sortBy('sort'))),
        ];
    }
}
