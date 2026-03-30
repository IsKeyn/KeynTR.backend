<?php

namespace App\Http\Resources\Game;

use App\Http\Resources\AnonsDateResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\Admin\AdminLinkResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ReleaseDateResource;
use App\Http\Resources\TagResource;
use App\Models\Game;
use App\Models\Media;
use App\Models\VotesLog;
use App\Services\VotesService;
use Illuminate\Http\Resources\Json\JsonResource;

class GameShortResource extends JsonResource
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
            'platforms' => $this->gamePlatform,
            'description' => $this->description,
            'active' => $this->active,
            'show_in_list' => $this->show_in_list,
            'release_dates' => ReleaseDateResource::collection($this->dates),
            'anons_dates' => AnonsDateResource::collection($this->anonsDates),
            'title_image' => $image ? MediaResource::make($image) : null,
            'covers' => $covers ? MediaResource::collection($covers) : null,
            'tags' => TagResource::collection($this->tags),
            'groups' => GroupResource::collection($this->groups),
            'genres' => GenreResource::collection($this->genres),
            'companies' => CompanyResource::collection($this->company),
            'links' => AdminLinkResource::collection($this->link),
            'additional_fields' => $this->additionalFields,
            'views' => $this->views ? $this->views->value : null,
            'likes' => $this->likes ? $this->likes->value : null,
            'already_voted' => VotesService::alreadyVoted($this->model, $this->id, VotesLog::LIKE, $request->user() ? $request->user()->id : null),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
