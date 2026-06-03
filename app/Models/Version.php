<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\VersionType;

class Version extends Model
{
    use HasFactory, ExtendModelTrait;

    public const CACHE_NAME = 'version';
    public const TABLE_NAME = 'versions';

    const TYPE_CREATE = 1;
    const TYPE_UPDATE = 2;
    const TYPE_SOFT_DELETE = 3;
    const TYPE_RECOVERY = 4;
    const TYPE_DELETE = 5;

    protected $fillable = [
        'data',
        'name',
        'entity_type',
        'entity_id',
        'do_type',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'active' => 'boolean',
        'do_type' => VersionType::class,
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
