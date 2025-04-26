<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGameItemResource extends JsonResource
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
            'description' => $this->description,
            'board_game_id' => $this->board_game_id,
            'image' => $image ? MediaResource::make($image) : null,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
