<?php

namespace App\Http\Resources\Admin\BoardGame;

use App\Http\Resources\BlockResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\SettingResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_close' => $this->is_close,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,

            'settings' => $this->whenLoaded('settings', SettingResource::collection($this->settings)),
            'media' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->first())),

            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),
            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),

            'created_by' => $this->created_by,
            'sort' => $this->sort,
            'active' => $this->active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
