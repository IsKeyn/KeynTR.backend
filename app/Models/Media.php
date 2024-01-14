<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    const TITLE_TYPE = 1;

    protected $fillable = [
        'name',
        'description',
        'type',
        'file_name',
        'mime_type',
        'size',
    ];

    public function getUrlAttribute()
    {
        return '/storage/media/' . $this->id . '/' . $this->file_name;
    }
}
