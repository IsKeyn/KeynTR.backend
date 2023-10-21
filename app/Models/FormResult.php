<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'data'
    ];

    protected $dates = [
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'data' => 'array'
    ];

    protected $hidden = [
        'updated_at'
    ];
}
