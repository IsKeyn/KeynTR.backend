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
            'description' => $this->description ?? null,
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
            'tags' => $this->when(
                $this->relationLoaded('tags') && $this->tags,
                fn() => TagResource::collection($this->tags)
            ),
            'seo' => $this->when(
                $this->relationLoaded('seo') && $this->seo,
                fn() => SeoResource::make($this->seo)
            ),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'menu' => $this->when(
                $this->relationLoaded('menu') && $this->menu,
                fn() => MenuTypeResource::collection($this->menu)
            ),
            'blocks' => $this->when(
                $this->relationLoaded('blocks') && $this->blocks,
                fn() => BlockResource::collection($this->blocks)
            ),
        ];
    }
}
