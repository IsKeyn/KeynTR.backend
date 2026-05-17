<?php

namespace App\Http\Resources\Admin\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'site_id' => $this->site_id,
            'name' => $this->name,
            'code' => $this->code,
            'value' => $this->value,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'sort' => $this->sort,
            'active' => $this->active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
