<?php

namespace App\Http\Resources\Messenger;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),

            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'reply_to_id' => $this->reply_to_id,
            'type' => $this->type,
            'body' => $this->body,
        ];
    }
}
