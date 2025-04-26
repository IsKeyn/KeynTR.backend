<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGamePlayerTimer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'time_start',
        'time_stop',
        'created_by',
    ];
}
