<?php

namespace App\Http\Resources\Admin\BoardGame;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGameResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'settings' => $this->settings,
            'media' => $this->title_image ? MediaResource::make($this->title_image) : null,
            'active' => $this->active,
            'is_close' => $this->is_close,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
