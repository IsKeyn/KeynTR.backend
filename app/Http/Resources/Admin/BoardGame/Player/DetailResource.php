<?php

namespace App\Http\Resources\Admin\BoardGame\Player;

use App\Http\Resources\BlockResource;
use App\Http\Resources\MenuTypeResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'user_id' => $this->user_id,
            'board_game_id' => $this->board_game_id,
            'points' => $this->points,
            'points_per_hour' => $this->points_per_hour,
            'place' => $this->place,
            'item_roll_count' => $this->item_roll_count,
            'step_count' => $this->step_count,
            'streak' => $this->streak,
            'active' => $this->active,
            'not_active_reason' => $this->not_active_reason,
            'added_games' => $this->added_games,
            'settings' => $this->settings,
            'premium' => $this->premium,
            'sort' => $this->sort,
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'seo' => $this->whenLoaded('seo', function() {
                return $this->seo && $this->seo->count() ? SeoResource::make($this->seo) : null;
            }),
            'menu' => $this->whenLoaded('menu', MenuTypeResource::collection($this->menu)),
            'blocks' => $this->whenLoaded('blocks', BlockResource::collection($this->blocks)),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
