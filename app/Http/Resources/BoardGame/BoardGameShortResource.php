<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardGameShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function __construct($resource, $additionalData = null)
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    public function toArray($request)
    {
        $image = $this->titleImage()->wherePivot('type', '=', Media::TITLE_TYPE)->first();

        return [
            'id' => $this->id,
            'entity_type' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $image ? MediaResource::make($image) : null,
            'active' => $this->active,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'player' => $this->additionalData ? BoardGamePlayerShortResource::make($this->additionalData) : null,
        ];
    }
}
