<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\MediaResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $mediaGroup = $this->mediaGroup()->orderBy('sort')->get();

        $page = $this->page->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'media_group' => $mediaGroup ? MediaResource::collection($mediaGroup) : null,
            'active' => $this->active,
            'page' => $page ? $page->id : null,
            'theme' => $this->theme,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
