<?php

namespace App\Http\Resources\Admin\Role;

use App\Http\Resources\Admin\ForExtension\AdminPermissionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TagResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'name' => $this->name,
            'system_name' => $this->system_name,
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
