<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardPositionEffect extends Model
{
    protected $table = 'bg_board_position_effects';

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'action',
        'media',
        'sort',
        'active',
        'created_by',
    ];
}
