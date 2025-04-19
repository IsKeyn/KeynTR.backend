<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class SlideResource extends JsonResource
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
            'image' => $image ? MediaResource::make($image) : null,
            'url' => $this->url,
            'type' => $this->type,
            'active' => $this->active,
            'tags' => TagResource::collection($this->tags),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
