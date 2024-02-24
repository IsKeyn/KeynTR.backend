<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAgentData extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'user_agent',
        'http_referer',
        'device',
        'platform',
        'browser',
        'robot',
        'entity_type',
        'entity_id'
    ];
}
