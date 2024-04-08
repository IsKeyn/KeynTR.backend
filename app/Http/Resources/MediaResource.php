<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'entity_type' => $this->model,
            'name' => $this->name,
            'description' => $this->description,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'src' => $this->url,
            'type' => $this->type,
            'tags' => TagResource::collection($this->tags),
            'user_info' => UserLightResource::make(User::query()->where('id', $this->created_by)->first()),
            'comments_count' => $this->comments->count(),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
