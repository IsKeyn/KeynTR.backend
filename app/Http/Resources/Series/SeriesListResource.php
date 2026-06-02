<?php

namespace App\Http\Resources\Series;

use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesListResource extends JsonResource
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
            'covers' => $this->getCovers(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getCovers()
    {
        if ($this->relationLoaded('cover')) {
            $covers = $this->cover()->orderByPivot('sort')->get();

            if ($covers->isNotEmpty()) {
                return ShortMediaResource::collection($covers);
            }
        }

        if ($this->relationLoaded('games') && $this->games->isNotEmpty()) {
            foreach ($this->games as $game) {
                if ($game->relationLoaded('cover') && $game->cover->isNotEmpty()) {
                    return ShortMediaResource::collection($game->cover);
                }
            }
        }
    }
}
