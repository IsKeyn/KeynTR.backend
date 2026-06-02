<?php

namespace App\Http\Resources;

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
        $group = $this->resolveCompanyGroup();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'company_role' => $group ? GroupResource::make($group) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function resolveCompanyGroup()
    {
        if (!$this->pivot || !$this->pivot->company_bind_type || !$this->pivot->company_bind_id) {
            return null;
        }

        return $this->group($this->pivot->company_bind_id, $this->pivot->company_bind_type)->first();
    }
}
