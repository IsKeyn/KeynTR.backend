<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\Media\ShortMediaResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGameShortestWithImageResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'media' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->wherePivot('type', '=', Media::TITLE_TYPE)->first())),
            'active' => $this->active,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
        ];
    }
}
