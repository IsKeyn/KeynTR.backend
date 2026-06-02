<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'name',
        'url',
        'description',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
