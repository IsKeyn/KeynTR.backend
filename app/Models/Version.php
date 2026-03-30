<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'data',
        'entity_type',
        'entity_id',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'active' => 'boolean',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
