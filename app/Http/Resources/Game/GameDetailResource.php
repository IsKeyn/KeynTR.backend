<?php

namespace App\Http\Resources\Game;

use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\BlockResource;
use App\Http\Resources\Company\CompanyWithGroupResource;
use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\Date\DateWithPlatformResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\Menu\MenuTypeResource;
use App\Http\Resources\Person\PersonListResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\Series\SeriesWithGamesResource;
use App\Http\Resources\TagResource;
use App\Models\Game;
use Illuminate\Http\Resources\Json\JsonResource;

class GameDetailResource extends JsonResource
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
            'entity_type' => Game::class,
            'active' => $this->active,
            'show_in_list' => $this->show_in_list,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage()->first())),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover()->orderByPivot('sort')->get())),
            'platforms' => $this->whenLoaded('gamePlatform', $this->gamePlatform),
            'release_dates' => $this->whenLoaded('dates', DateWithPlatformResource::collection($this->dates)),
            'anons_dates' => $this->whenLoaded('anonsDates', DateShortResource::collection($this->anonsDates)),
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'groups' => $this->whenLoaded('groups', GroupResource::collection($this->groups)),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
            'companies' => $this->whenLoaded('company', CompanyWithGroupResource::collection($this->company)),
            'links' => $this->whenLoaded('link', AdminLinkResource::collection($this->link)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'series' => $this->whenLoaded('series', SeriesWithGamesResource::collection($this->series)),
            'people' => $this->whenLoaded('people', PersonListResource::collection($this->people)),
            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),
            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
