<?php

namespace App\Http\Resources\Admin\Article;

use App\Http\Resources\Admin\ForExtension\AdminCompanyResource;
use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\Admin\ForExtension\AdminPeopleResource;
use App\Http\Resources\BlockResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
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
            'text_preview' => $this->text_preview,
            'text_full' => $this->text_full,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,

            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),

            'people' => $this->whenLoaded('people', AdminPeopleResource::collection($this->people)),

            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'companies' => $this->whenLoaded('company', AdminCompanyResource::collection($this->company)),
            'links' => $this->whenLoaded('link', AdminLinkResource::collection($this->link)),

            'type' => $this->type,
            'sort' => $this->sort,
            'active' => $this->active,

            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),

            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
