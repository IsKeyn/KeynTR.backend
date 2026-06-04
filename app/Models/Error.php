<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'type',
        'from'
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s'
    ];

    protected $hidden = [
        'updated_at'
    ];
}
