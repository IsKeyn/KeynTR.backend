<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Admin\ForExtension\AdminCompanyResource;
use App\Http\Resources\Admin\ForExtension\AdminGameResource;
use App\Http\Resources\Admin\ForExtension\AdminGenreResource;
use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSeriesResource extends JsonResource
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
            'model' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort' => $this->sort,
            'active' => $this->active,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),
            'spc_id' => $this->spc_id,

            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),

            'game' => $this->whenLoaded('games', AdminGameResource::collection($this->games)),
            'genres' => $this->whenLoaded('genres', AdminGenreResource::collection($this->genres)),

            'companies' => $this->whenLoaded('company', AdminCompanyResource::collection($this->company)),
            'links' => $this->whenLoaded('link', AdminLinkResource::collection($this->link)),

            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),

            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),

            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
