<?php

namespace App\Http\Resources\Admin\Messenger\Message;

use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'reply_to_id' => $this->reply_to_id,
            'type' => $this->type,
            'body' => $this->body,
        ];
    }
}
