<?php

namespace App\Http\Resources\Admin\Person;

use App\Http\Resources\Admin\ForExtension\AdminGameResource;
use App\Http\Resources\Admin\SeoResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPersonResource extends JsonResource
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

            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),

            'game' => $this->whenLoaded('game', AdminGameResource::collection($this->game)),

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
