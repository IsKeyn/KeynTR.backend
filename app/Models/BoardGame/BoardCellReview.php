<?php

namespace App\Models\BoardGame;

use App\Models\Comments;
use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardCellReview extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_board_cell_reviews';

    public const CACHE_NAME = 'bg-board-cell-reviews';
    public const TABLE_NAME = 'bg_board_cell_reviews';

    public const CREATE_VERSION = false;

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BoardCellReviewCacheService';
//    public const FILTER = 'App\Filters\BoardGame\BgPlayerPositionFilter';
    public const SERVICE = 'App\Services\BoardGame\BoardCellService';

    // Resource for admin panel
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BoardCellReview\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BoardCellReview\ListResource';

    // Resource for public
    public const PUBLIC_RESOURCES = [
        'App\Http\Resources\BoardGame\Board\Cell\ReviewResource',
    ];

    protected $fillable = [
        'player_id',
        'user_id',
        'board_game_id',
        'board_position_effects_id',
        'comment_id',
        'completion_time_seconds',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comments::class, 'comment_id');
    }

    public function boardPositionEffect(): BelongsTo
    {
        return $this->belongsTo(BoardPositionEffect::class, 'board_position_effects_id');
    }
}
