<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\SettingResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BgLayoutResource extends JsonResource
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

            'entity_type' => $this->model ?? null,
            'description' => $this->description,
            'is_close' => $this->is_close,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,

            'settings' => $this->whenLoaded('settings', SettingResource::collection($this->settings)),
            'media' => $this->whenLoaded('media', ShortMediaResource::make($this->media()->first())),
        ];
    }
}
