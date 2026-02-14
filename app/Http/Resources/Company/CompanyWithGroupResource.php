<?php

namespace App\Http\Resources\Company;

use App\Http\Resources\GroupResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyWithGroupResource extends JsonResource
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
            'company_role' => $this->getCompanyRole(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getCompanyRole()
    {
        if (!$this->relationLoaded('pivot') || !$this->pivot) {
            return new \Illuminate\Http\Resources\MissingValue();
        }

        $entity = $this->pivot->company_bind_type;

        if ($entity === 'App\Models\Game') {
            $game = $this->game()->first();
            $group = $game ? $this->group($game->id, get_class($game))->first() : null;
            return $group ? GroupResource::make($group) : null;
        }

        if ($entity === 'App\Models\Movie') {
            $movie = $this->movie()->first();
            $group = $movie ? $this->group($movie->id, get_class($movie))->first() : null;
            return $group ? GroupResource::make($group) : null;
        }

        return null;
    }
}
