<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BlockResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\TagResource;
use App\Models\Article;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $image = $this->titleImage()->wherePivot('type', '=', Media::TITLE_TYPE)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'text_preview' => $this->text_preview,
            'text_full' => $this->text_full,
            'title_image' => $image ? MediaResource::make($image) : null,
            'type' => $this->type,
            'tags' => TagResource::collection($this->tags),
            'views' => $this->views ? $this->views->value : null,
            'likes' => $this->likes ? $this->likes->value : null,
            'comments_count' => $this->comments->count(),
            'entity_type' => Article::class,
            'blocks' => BlockResource::collection($this->blocks),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
