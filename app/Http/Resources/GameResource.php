<?php

namespace App\Http\Resources;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Admin\LinkResource;
use App\Http\Resources\ReleaseDateResource;
use App\Models\Game;
use App\Models\Media;
use App\Models\VotesLog;
use App\Services\VotesService;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
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
        $covers = $this->media()->wherePivot('type', '=', Media::COVER_TYPE)->get();

        return [
            'id' => $this->id,
            'entity_type' => Game::class,
            'name' => $this->name,
            'slug' => $this->slug,
            'platforms' => $this->platforms,
            'description' => $this->description,
            'release_dates' => ReleaseDateResource::collection($this->dates),
            'title_image' => $image ? MediaResource::make($image) : null,
            'covers' => $covers ? MediaResource::collection($covers) : null,
            'tags' => TagResource::collection($this->tags),
            'genres' => GenreResource::collection($this->genres),
            'companies' => CompanyResource::collection($this->company),
            'links' => LinkResource::collection($this->link),
            'additional_fields' => $this->additionalFields,
            'seo' => $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null,
            'views' => $this->views ? $this->views->value : null,
            'likes' => $this->likes ? $this->likes->value : null,
            'menu' => MenuTypeResource::collection($this->menu),
            'already_voted' => VotesService::alreadyVoted($this->model, $this->id, VotesLog::LIKE, $request->user() ? $request->user()->id : null),
            'comments_count' => $this->comments->count(),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
