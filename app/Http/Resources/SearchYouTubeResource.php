<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class SearchYouTubeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        setlocale(LC_TIME , 'russian.UTF-8');

        return [
            'id' => $this->id,
            'video_id' => $this->video_id,
            'published_at' => Carbon::parse($this->published_at)->formatLocalized('%d %B %Y') . ' г.',
            'thumbnails' => json_decode($this->thumbnails),
            'title' => $this->title,
            'description' => nl2br($this->description),
        ];
    }
}
