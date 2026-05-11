<?php

namespace App\Http\Resources\Admin\Menu;

use App\Http\Resources\Admin\ForExtension\AdminPermissionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TagResource;

class DetailResource extends JsonResource
{
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
            'permissions' => $this->whenLoaded('permissions', AdminPermissionResource::collection($this->permissions)),
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
