<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    public const CACHE_NAME = 'setting';
    public const TABLE_NAME = 'settings';

    public const CACHE_SERVICE = 'App\Services\Cache\SettingCacheService';
    public const FILTER = 'App\Filters\BoardGame\SettingFilter';
    public const SERVICE = 'App\Services\SettingService';

    /* Resource for admin panel */
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\Setting\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\Setting\ListResource';

    /* Resource for public */
    public const PUBLIC_RESOURCES = [];

    protected $fillable = [
        'site_id',
        'name',
        'code',
        'value',
        'entity_type',
        'entity_id',
        'sort',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
