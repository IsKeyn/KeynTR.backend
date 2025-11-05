<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $group = null;
        $entity = null;

        if ($this->pivot) {
            $entity = $this->pivot->company_bind_type;
        }

        if ($entity === 'App\Models\Game' && $game = $this->game()->first()) {
            $group = $this->group($game->id, get_class($game))->first();
        }

        if ($entity === 'App\Models\Movie' && $movie = $this->movie()->first()) {
            $group = $this->group($movie->id, get_class($movie))->first();
        }

        return [
            'company' => $this->id,
            'company_role' => $group ? $group->id : null,
            'additional_info' => $this?->pivot?->additional_info,
        ];
    }
}
