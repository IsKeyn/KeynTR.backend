<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_boards';

    public const CACHE_NAME = 'bg-board';
    public const TABLE_NAME = 'bg_boards';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgBoardCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgBoardFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgBoard\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgBoard\ListResource';
    public const SERVICE = 'App\Services\BoardGame\BoardService';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'columns',
        'media',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
