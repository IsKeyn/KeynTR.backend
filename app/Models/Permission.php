<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'system_name',
        'entity_type',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
