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
            'menu_type_bind_id' => $this->group_icon,
            'menu_type_bind_type' => $this->group_icon,
            'sort' => $this->group_icon,
            'active' => $this->group_icon,
            'elements' => MenuElementsResource::collection($this->elements->sortBy('sort')),
        ];
    }
}
