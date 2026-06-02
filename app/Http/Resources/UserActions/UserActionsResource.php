<?php

namespace App\Http\Resources\UserActions;

use App\Models\VotesLog;
use App\Services\VotesService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActionsResource extends JsonResource
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
            'views' => $this->whenLoaded('views', function() {
                return $this->views ? $this->views->value : null;
            }),
            'likes' => $this->whenLoaded('likes', function() {
                return $this->likes ? $this->likes->value : null;
            }),
            'already_voted' => VotesService::alreadyVoted($this->model, $this->id, VotesLog::LIKE, $request->user() ? $request->user()->id : null),
            'comments_count' => $this->whenLoaded('comments', $this->comments->count()),
        ];
    }
}
