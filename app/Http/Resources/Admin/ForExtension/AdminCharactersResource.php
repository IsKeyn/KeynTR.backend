<?php

namespace App\Http\Resources\Admin\ForExtension;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminCharactersResource extends JsonResource
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
            'character' => $this->id,
            'character_role' => $this->resolveRoleId(),
        ];
    }

    private function resolveRoleId(): ?int
    {
        if (!$this->pivot || !$this->pivot->character_bind_type || !$this->pivot->character_bind_id) {
            return null;
        }

        $group = $this->group($this->pivot->character_bind_id, $this->pivot->character_bind_type)->first();

        return $group?->id;
    }
}
