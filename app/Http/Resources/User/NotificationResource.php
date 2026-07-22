<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::where('id', $this->created_by)->first();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'actions' => $this->actions,
            'viewed' => $this->viewed,
            'entity' => $this->entity,
            'from' => UserResource::make($user),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
