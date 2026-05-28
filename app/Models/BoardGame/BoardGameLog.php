<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardGameLog extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-logs';
    public const TABLE_NAME = 'board_game_logs';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgLogCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgLogFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgLog\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgLog\ListResource';
    public const SERVICE = 'App\Services\BoardGame\LogService';

    protected $fillable = [
        'message',
        'board_game_id',
        'sort',
        'active',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
