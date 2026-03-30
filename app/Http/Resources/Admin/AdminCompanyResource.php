<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminCompanyResource extends JsonResource
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
            'company' => $this->id,
            'company_role' => $this->resolveCompanyRoleId(),
            'additional_info' => $this?->pivot?->additional_info,
        ];
    }

    private function resolveCompanyRoleId(): ?int
    {
        if (!$this->pivot || !$this->pivot->company_bind_type || !$this->pivot->company_bind_id) {
            return null;
        }

        $group = $this->group($this->pivot->company_bind_id, $this->pivot->company_bind_type)->first();

        return $group?->id;
    }
}
