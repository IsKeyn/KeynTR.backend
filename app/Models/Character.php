<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Character extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    public const CACHE_NAME = 'character';
    public const TABLE_NAME = 'characters';

    public const CACHE_SERVICE = 'App\Services\Cache\CharacterCacheService';
    public const FILTER = 'App\Filters\CharacterFilter';
    public const SERVICE = 'App\Services\CharacterService';

    public const OBSERVER = 'App\Observers\CharacterObserver';

    public const ADMIN_CONTROLLER = 'App\Http\Controllers\Admin\AdminCharacterController';
    public const REQUEST = 'App\Http\Requests\CharacterRequest';

    // Resource for admin panel
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\Character\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\Character\ListResource';

    // Resource for public
    public const PUBLIC_DETAIL_RESOURCE = 'App\Http\Resources\Admin\Character\DetailResource';
    public const PUBLIC_LIST_RESOURCE = 'App\Http\Resources\Admin\Character\ListResource';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function games()
    {
        return $this->morphedByMany(Game::class, 'character_bind')->withTimestamps();
    }
}
