<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\VotesLog;
use App\Services\MediaService;
use App\Services\VotesService;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $mediaService = new MediaService();

        return [
            'id' => $this->id,
            'entity_type' => $this->model,
            'name' => $this->name,
            'description' => $this->description,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'src' => $this->url,
            'webp' => $mediaService->getWebp($this),
            'resized' => $mediaService->getResizes($this),
            'type' => $this->type,
            'tags' => TagResource::collection($this->tags),
            'user_info' => UserLightResource::make(User::query()->where('id', $this->created_by)->first()),
            'views' => $this->views ? $this->views->value : null,
            'likes' => $this->likes ? $this->likes->value : null,
            'already_voted' => VotesService::alreadyVoted($this->model, $this->id, VotesLog::LIKE, $request->user() ? $request->user()->id : null),
            'comments_count' => $this->comments->count(),
            'sort' => $this->pivot ? $this->pivot->sort : '',
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
