<?php

namespace App\Http\Resources\Character;

use App\Http\Resources\GroupResource;
use App\Models\Game;
use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

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
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'entity_type' => Game::class,
            'role' => $group ? GroupResource::make($group) : null,
            'covers' => $this->whenLoaded('cover', fn() => ShortMediaResource::collection($this->cover)),
        ];
    }

    private function resolveGroup()
    {
        if (!$this->pivot || !$this->pivot->character_bind_id || !$this->pivot->character_bind_type) {
            return null;
        }

        return $this->group($this->pivot->character_bind_id, $this->pivot->character_bind_type)->first();
    }
}
