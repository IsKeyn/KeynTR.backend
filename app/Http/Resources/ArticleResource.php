<?php

namespace App\Http\Resources;

use App\Models\Article;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ArticleResource extends JsonResource
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

        $type = $this->type ? $this->type : 'news';

        return [
            'id' => $this->id,
            'entity' => Article::class,
            'code' => '/' . $type . '/' . $this->code,
            'title' => $this->title,
            'text_full' => $this->text_full,
            'image' => $this->image,
            'tags' => $this->tags,
            'created_at' => Carbon::parse($this->created_at)->formatLocalized('%d %B %Y') . ' г.',
            'comments_count' => $this->comments->count(),
        ];
    }
}
