<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ArticleListResource extends JsonResource
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
            'code' => '/news/' . $this->code,
            'title' => $this->title,
            'text_preview' => $this->text_preview,
            'image' => $this->image,
            'tags' => $this->tags,
            'created_at' => Carbon::parse($this->created_at)->formatLocalized('%d %B %g'),
            'comments_count' => $this->comments->count()
        ];
    }
}
