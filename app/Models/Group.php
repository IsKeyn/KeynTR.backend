<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    public const CACHE_NAME = 'group';
    public const TABLE_NAME = 'groups';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'entity_type',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
