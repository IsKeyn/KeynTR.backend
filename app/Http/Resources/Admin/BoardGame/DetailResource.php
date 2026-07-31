<?php

namespace App\Http\Resources\Admin\BoardGame;

use App\Http\Resources\Media\ShortMediaResource;
use App\Http\Resources\SettingResource;
use App\Traits\CommonResourceFields;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    use CommonResourceFields;

    public function toArray($request)
    {
        return [
            ...$this->commonFields(),
            ...$this->commonLoadedFields(),

            'is_close' => $this->is_close,
            'is_test' => $this->is_test,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,

            'settings' => $this->whenLoaded('settings', fn() => SettingResource::collection($this->settings)),
            'media' => $this->whenLoaded('media', fn() => ShortMediaResource::make($this->media->first())),
        ];
    }
}
