<?php

namespace App\Http\Resources\Admin\Notification;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'actions' => $this->actions,
            'viewed' => $this->viewed,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
