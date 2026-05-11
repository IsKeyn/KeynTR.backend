<?php

namespace App\Http\Resources\Admin\User;

use App\Http\Resources\Admin\ForExtension\AdminRoleResource;
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
            'public_name' => $this->public_name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'settings' => $this->settings,
            'sort' => $this->sort,
            'is_admin' => $this->is_admin,
            'active' => $this->active,
            'roles' => $this->whenLoaded('roles', AdminRoleResource::collection($this->roles)),
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
