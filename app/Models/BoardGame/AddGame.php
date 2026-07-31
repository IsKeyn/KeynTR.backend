<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddGame extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_add_games';

    public const CACHE_NAME = 'bg-add-games';
    public const TABLE_NAME = 'bg_add_games';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgAddGameCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgAddGameFilter';
    public const SERVICE = 'App\Services\BoardGame\BgAddGameService';
    public const OBSERVER = 'App\Observers\BoardGame\BgAddGameObserver';

    public const ADMIN_CONTROLLER = 'App\Http\Controllers\Admin\BoardGame\BgAddGameController';
    public const REQUEST = 'App\Http\Requests\BoardGame\BgAddGameRequest';

    // Resource for admin panel
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\AddGame\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\AddGame\ListResource';

    // Resource for public
    public const PUBLIC_RESOURCES = [];

    const STATUS_CAN_ADD = 1;
    const STATUS_CANT_ADD = 2;
    const STATUS_ALREADY_ADDED = 3;

    const ADD_STATUS_DRAFT = 0; // Черновик
    const ADD_STATUS_SENT = 1; // Отправлен на рассмотрение
    const ADD_STATUS_UNDER_CONSIDERATION = 2; // Рассматривается
    const ADD_STATUS_ADDED = 3; // Добавлена
    const ADD_STATUS_DENIED = 4; // Отказана
    const ADD_STATUS_RETURNED = 5; // Возвращена

    protected $fillable = [
        'bg_player_id',
        'user_id',
        'board_game_id',
        'name',
        'gaming_platform_id',
        'coop',
        'game_completion_time',
        'difficulty',
        'description',
        'comment_for_moderator',
        'moderator_comment',
        'status',
        'sort',
        'active',
    ];

    protected $casts = [
        'coop' => 'boolean',
        'active' => 'boolean',
    ];
}
