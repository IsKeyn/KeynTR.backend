<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class YouTubeResource extends JsonResource
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
            'id' => $this->id,
            'video_id' => $this->video_id,
            'published_at' => $this->published_at,
            'thumbnails' => $this->thumbnails,
        ];
    }
}
