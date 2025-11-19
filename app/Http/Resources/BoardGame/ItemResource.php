<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\MediaResource;
use App\Http\Resources\UserPublicResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
        $sound = $this->titleImage()->wherePivot('type', '=', Media::SOUND)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'actions' => json_decode($this->actions),
            'type' => $this->type,
            'drop_chance' => $this->drop_chance,
            'board_game_id' => $this->board_game_id,
            'image' => $image ? MediaResource::make($image) : null,
            'sound' => $image ? MediaResource::make($sound) : null,
            'active' => $this->active,
            'authorUser' => $this->authorUser ? UserPublicResource::make($this->authorUser) : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'additional_data' => $this->additional_data,
        ];
    }
}
