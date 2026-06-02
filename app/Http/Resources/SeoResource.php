<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $keywords = '';

        if ($this->keywords) {
            $keywords = $this->keywords;
        } else {
            if ($this->entity->tags) {
                foreach ($this->entity->tags as $tag) {
                    if ($keywords) {
                        $keywords .= ', ';
                    }

                    $keywords .= $tag->name;
                }
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $keywords,
        ];
    }
}
