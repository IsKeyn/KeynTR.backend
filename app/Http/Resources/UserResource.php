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
        $image = $this->avatar()->wherePivot('type', '=', Media::TITLE_TYPE)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $image ? MediaResource::make($image) : null,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => RoleResource::collection($this->roles),
            'additional_fields' => $this->additionalFields,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
