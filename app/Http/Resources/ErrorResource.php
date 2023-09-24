<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
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
            'message' => $this->message,
            'from' => $this->from,
            'created_at' => $this->created_at->format('d.m.Y H:i:s'),
            'type' => $this->when(
                $this->type === 'public',
                function() {
                    return 'Frontend error';
                },
                function() {
                    return $this->type;
                }
            ),
        ];
    }
}
