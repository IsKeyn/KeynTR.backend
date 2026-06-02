<?php

namespace App\Http\Resources\Person;

use App\Http\Resources\GroupResource;
use App\Models\Game;
use App\Http\Resources\Media\ShortMediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $group = $this->resolveGroup();

        return [
            'id' => $this->id,
            'entity_type' => Game::class,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'role' => $group ? GroupResource::make($group) : null,
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function resolveGroup()
    {
        if (!$this->pivot || !$this->pivot->person_bind_id || !$this->pivot->person_bind_type) {
            return null;
        }

        return $this->group($this->pivot->person_bind_id, $this->pivot->person_bind_type)->first();
    }
}
