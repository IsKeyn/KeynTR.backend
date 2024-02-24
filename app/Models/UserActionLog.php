<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'created_by',
    ];

    protected $casts = [
        'message' => 'array',
    ];

    public function userAgentData()
    {
        return $this->morphMany(UserAgentData::class, 'entity');
    }
}
