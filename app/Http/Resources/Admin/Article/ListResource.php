<?php

namespace App\Http\Resources\Admin\Article;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'text_preview' => $this->text_preview,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'type' => $this->type,
            'active' => $this->active,
            'sort' => $this->sort,
            'created_by' => $this->created_by,
            'editor' => $this->editor,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
