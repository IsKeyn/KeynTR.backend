<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\TagResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
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
            'name' => $this->name,
            'url' => $this->url,
            'sort' => $this->sort,
            'description' => $this->description,
            'active' => $this->active,
            'media_id' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->wherePivot('type', '=', Media::TITLE_TYPE)->first())),
            'tags' => TagResource::collection($this->tags),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
