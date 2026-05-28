<?php

namespace App\Http\Resources\Admin\BoardGame;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'media' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->first())),
            'is_close' => $this->is_close,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_by' => $this->created_by,
            'sort' => $this->sort,
            'active' => $this->active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
