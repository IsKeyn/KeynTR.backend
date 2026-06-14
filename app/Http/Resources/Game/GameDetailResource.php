<?php

namespace App\Http\Resources\Game;

use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\Company\CompanyWithGroupResource;
use App\Http\Resources\Date\DateShortResource;
use App\Http\Resources\Date\DateWithPlatformResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\Person\PersonListResource;
use App\Http\Resources\Series\SeriesWithGamesResource;
use App\Models\Game;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class GameDetailResource extends JsonResource
{
    use CommonResourceFields;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'entity_type' => Game::class,
            'show_in_list' => $this->show_in_list,
            'title_image' => $this->whenLoaded('titleImage', ShortMediaResource::make($this->titleImage)),
            'covers' => $this->whenLoaded('cover', ShortMediaResource::collection($this->cover)),
            'platforms' => $this->whenLoaded('gamePlatform', $this->gamePlatform),
            'release_dates' => $this->whenLoaded('dates', DateWithPlatformResource::collection($this->dates)),
            'anons_dates' => $this->whenLoaded('anonsDates', DateShortResource::collection($this->anonsDates)),
            'groups' => $this->whenLoaded('groups', GroupResource::collection($this->groups)),
            'genres' => $this->whenLoaded('genres', GenreResource::collection($this->genres)),
            'companies' => $this->whenLoaded('company', CompanyWithGroupResource::collection($this->company)),
            'links' => $this->whenLoaded('link', AdminLinkResource::collection($this->link)),
            'additional_fields' => $this->whenLoaded('additionalFields', $this->additionalFields),
            'series' => $this->whenLoaded('series', SeriesWithGamesResource::collection($this->series)),
            'people' => $this->whenLoaded('people', PersonListResource::collection($this->people)),
        ];
    }
}
