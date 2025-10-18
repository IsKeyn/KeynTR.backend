<?php

namespace App\Http\Resources\BoardGame;

use App\Http\Resources\BlockResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\SeoResource;
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
        $data = [
            'id' => $this->id,
            'entity_type' => $this->model,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'media' => $this->title_image ? MediaResource::make($this->title_image) : null,
            'seo' => $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null,
            'blocks' => BlockResource::collection($this->blocks),
            'active' => $this->active,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
        ];

        if (isset($this->additionalData['player'])) {
            $data['player'] = BoardGamePlayerShortResource::make($this->additionalData['player']);
        }

        return $data;
    }
}
