<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $table = 'bg_boards';

    use HasFactory, ExtendModelTrait;

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
