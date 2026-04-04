<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminGameListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort' => $this->sort,
            'active' => $this->active,
            'show_in_list' => $this->show_in_list,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
