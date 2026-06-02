<?php

namespace App\Traits;

use App\Http\Resources\BlockResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;

trait CommonResourceFields
{
    protected function commonFields(): array
    {
        return [
            'id' => $this->id,
            'model' => $this->model ?? null,
            'name' => $this->name ?? null,
            'slug' => $this->slug ?? null,
            'active' => $this->active ?? null,
            'sort' => $this->sort ?? null,
            'created_by' => $this->created_by ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function commonLoadedFields(): array
    {
        return [
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),
            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),
        ];
    }
}
