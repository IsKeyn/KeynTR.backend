<?php

namespace App\Http\Resources\Media;

use App\Services\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortMediaResource extends JsonResource
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
            'sort' => $this->pivot ? $this->pivot->sort : '',
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
