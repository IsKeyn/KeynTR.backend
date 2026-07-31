<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Admin\ForExtension\AdminCharactersResource;
use App\Http\Resources\Admin\ForExtension\AdminPeopleResource;
use App\Http\Resources\Media\ShortMediaResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\ForExtension\AdminSeriesResource;
use App\Http\Resources\Admin\ForExtension\AdminGroupResource;
use App\Http\Resources\Admin\ForExtension\AdminAnonsDateResource;
use App\Http\Resources\Admin\ForExtension\AdminCompanyResource;
use App\Http\Resources\Admin\ForExtension\AdminGenreResource;
use App\Http\Resources\Admin\ForExtension\AdminLinkResource;
use App\Http\Resources\Admin\ForExtension\AdminReleaseDateResource;

class AdminGameResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'mod' => $this->mod,
            'show_in_list' => $this->show_in_list,
            'spc_id' => $this->spc_id,

            'series' => $this->whenLoaded('series', fn() => AdminSeriesResource::collection($this->series)),
            'people' => $this->whenLoaded('people', fn() => AdminPeopleResource::collection($this->people)),
            'characters' => $this->whenLoaded('characters', fn() => AdminCharactersResource::collection($this->characters)),
            'groups' =>  $this->whenLoaded('groups', fn() => AdminGroupResource::collection($this->groups)),
            'genres' => $this->whenLoaded('genres', fn() => AdminGenreResource::collection($this->genres)),
            'anons_dates' => $this->whenLoaded('anonsDates', fn() => AdminAnonsDateResource::collection($this->anonsDates)),
            'release_dates' => $this->whenLoaded('dates', fn() => AdminReleaseDateResource::collection($this->dates)),

            'companies' => $this->whenLoaded('company', fn() => AdminCompanyResource::collection($this->company)),
            'links' => $this->whenLoaded('link', fn() => AdminLinkResource::collection($this->link)),

            'title_image' => $this->whenLoaded('titleImage', fn() => ShortMediaResource::make($this->titleImage)),
            'covers' => $this->whenLoaded('cover', fn() => ShortMediaResource::collection($this->cover)),
        ];
    }
}
