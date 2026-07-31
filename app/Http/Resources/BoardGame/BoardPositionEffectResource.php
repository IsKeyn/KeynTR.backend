<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardPositionEffectResource extends JsonResource // TODO устравший ресурс, новый BgBoardPositionEffectResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'actions' => $this->actions,
            'title_image' => $this->title_image ? MediaResource::make($this->title_image) : null,
            'sort' => $this->sort,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
