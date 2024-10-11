<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Media;
use App\Models\VotesLog;
use App\Services\VotesService;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $image = $this->titleImage()->wherePivot('type', '=', Media::TITLE_TYPE)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'text_preview' => $this->text_preview,
            'text_full' => $this->text_full,
            'image' => $image ? MediaResource::make($image) : null,
            'type' => $this->ArticleType,
            'tags' => TagResource::collection($this->tags),
            'views' => $this->views ? $this->views->value : null,
            'likes' => $this->likes ? $this->likes->value : null,
            'already_voted' => VotesService::alreadyVoted($this->model, $this->id, VotesLog::LIKE, $request->user() ? $request->user()->id : null),
            'comments_count' => $this->comments->count(),
            'entity_type' => Article::class,
            'blocks' => BlockResource::collection($this->blocks),
            'seo' => $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null,
            'author' => UserResource::make($this->author),
            'editor' => UserResource::make($this->articleEditor),
            'show_author' => $this->show_author,
            'show_editor' => $this->show_editor,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}
