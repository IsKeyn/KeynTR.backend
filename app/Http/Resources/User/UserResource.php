<?php

namespace App\Http\Resources\User;

use App\Http\Resources\MediaResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'public_name' => $this->public_name,
            'avatar' => $this->whenLoaded('avatar', fn() => MediaResource::make($this->avatar->first())),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'settings' => $this->settings,
            'roles' => $this->whenLoaded('roles', fn() => RoleResource::collection($this->roles)),
            'additional_fields' => $this->whenLoaded('additionalFields', fn() => $this->additionalFields),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
