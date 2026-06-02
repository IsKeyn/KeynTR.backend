<?php

namespace App\Http\Resources\Series;

use App\Http\Resources\Game\GameListResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesWithGamesResource extends JsonResource
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
            'active' => $this->active,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'games' => $this->whenLoaded('games', GameListResource::collection($this->games)),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
