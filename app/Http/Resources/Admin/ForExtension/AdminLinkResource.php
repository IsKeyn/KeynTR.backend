<?php

namespace App\Http\Resources\Admin\ForExtension;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminLinkResource extends JsonResource
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
            'name' => $this->name,
            'url' => $this->url,
        ];
    }
}
