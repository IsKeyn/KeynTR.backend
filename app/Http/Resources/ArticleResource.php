<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'entity' => get_class($this),
            'code' => '/news/' . $this->code,
            'title' => $this->title,
            'text_full' => $this->text_preview,
            'image' => $this->image,
        ];
    }
}
