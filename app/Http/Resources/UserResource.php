<?php

namespace App\Http\Resources;

use App\Models\Media;
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
            'avatar' => $this->whenLoaded('avatar', MediaResource::make($this->avatar()->wherePivot('type', '=', Media::TITLE_TYPE)->first())),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'settings' => $this->settings,
            'roles' => $this->whenLoaded('roles', RoleResource::collection($this->roles)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
