<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'was_counted',
        'created_by',
    ];

    public function userAgentData()
    {
        return $this->morphMany(UserAgentData::class, 'entity');
    }
}
