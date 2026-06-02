<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminVersionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'data' => $this->data,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'do_type' => $this->do_type?->label(),
            'sort' => $this->sort,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
