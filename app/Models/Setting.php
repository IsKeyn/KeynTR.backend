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
}
