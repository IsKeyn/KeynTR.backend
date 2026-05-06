<?php

namespace App\Http\Resources\Series;

use App\Http\Resources\BlockResource;
use App\Http\Resources\Game\GameListResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\Menu\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesDetailResource extends JsonResource
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
            'active' => $this->active,
            'show_in_list' => $this->show_in_list,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),
            'games' => $this->whenLoaded('games', GameListResource::collection($this->games->where('active', true))),
            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
