<?php

namespace App\Http\Resources\Admin\GamingPlatform;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'release_date' => $this->release_date,
            'sort' => $this->sort,
            'active' => $this->active,
            'spc_id' => $this->spc_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
