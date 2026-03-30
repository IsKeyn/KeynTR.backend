<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWithAvatarResource extends JsonResource
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
            'avatar' => $this->whenLoaded('avatar', ShortMediaResource::make($this->avatar()->first())),
        ];
    }
}
