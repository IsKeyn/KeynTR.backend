<?php
namespace App\Http\Resources\Seo;

use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $keywords = '';

        if ($this->keywords) {
            $keywords = $this->keywords;
        } else {
            if ($this->relationLoaded('entity') && $this->entity) {
                $keywords = $this->whenLoaded('entity.tags', function() {
                    if ($this->entity && $this->entity->relationLoaded('tags') && $this->entity->tags) {
                        return $this->entity->tags->pluck('name')->implode(', ');
                    }
                    return '';
                }, '');
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $keywords,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
