<?php

namespace App\Http\Resources;

use App\Http\Resources\Permission\ShortResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuElementsResource extends JsonResource
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
            'url' => $this->url,
            'target' => $this->target,
            'menu_type_id' => $this->menu_type_id,
            'link_type' => $this->link_type,
            'icon' => $this->icon,
            'sort' => $this->sort,
            'active' => $this->active,
            'permissions' => $this->whenLoaded('permissions', ShortResource::collection($this->permissions)),
        ];
    }
}
