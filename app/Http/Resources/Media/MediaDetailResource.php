<?php

namespace App\Http\Resources\Media;

use App\Http\Resources\TagResource;
use App\Http\Resources\UserLightResource;
use App\Services\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $mediaService = new MediaService();

        return [
            'id' => $this->id,
            'entity_type' => $this->model,
            'name' => $this->name,
            'description' => $this->description,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'src' => $this->url,
            'webp' => $mediaService->getWebp($this),
            'resized' => $mediaService->getResizes($this),
            'type' => $this->type,
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'user_info' => $this->whenLoaded('user', UserLightResource::make($this->user)),
            'sort' => $this->pivot ? $this->pivot->sort : '',
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
