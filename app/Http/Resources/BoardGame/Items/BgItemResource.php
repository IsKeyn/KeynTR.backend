<?php

namespace App\Http\Resources\BoardGame\Items;

use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\User\UserPublicResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgItemResource extends JsonResource
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

            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'actions' => $this->actions,
            'type' => $this->type,
            'drop_chance' => $this->drop_chance,
            'price' => $this->price,
            'board_game_id' => $this->board_game_id,
            'image' => $this->when(
                $this->relationLoaded('titleImage') && $this->titleImage,
                fn() => ShortMediaResource::make($this->titleImage)
            ),
            'sound' => $this->when(
                $this->relationLoaded('sound') && $this->sound,
                fn() => ShortMediaResource::make($this->sound)
            ),
            'authorUser' => $this->when(
                $this->relationLoaded('authorUser') && $this->authorUser,
                fn() => UserPublicResource::make($this->authorUser)
            ),
        ];
    }
}
