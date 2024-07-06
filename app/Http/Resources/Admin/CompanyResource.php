<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($game = $this->game()->first()) {
            $group = $this->group($game->id, get_class($game))->first();
        }

        return [
            'company' => $this->id,
            'company_role' => $group ? $group->id : null,
        ];
    }
}
