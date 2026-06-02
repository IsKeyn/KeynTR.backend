<?php

namespace App\Http\Resources\Admin\GamingPlatform;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TagResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'release_date' => $this->release_date,
            'sort' => $this->sort,
            'active' => $this->active,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? \App\Http\Resources\Admin\SeoResource::make($this->seo) : null;
            }),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
