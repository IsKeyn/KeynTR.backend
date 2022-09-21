<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YouTubeApi extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'access_token'
    ];
}
