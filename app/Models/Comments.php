<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comments extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_name',
        'email',
        'message',
        'entity_type',
        'entity_id',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
