<?php

namespace App\Http\Resources\Admin\Messenger\Chat;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'type' => $this->type,
            'title' => $this->title,
            'last_message_id' => $this->last_message_id,
            'last_message_at' => $this->last_message_at,
        ];
    }
}
