<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    const PAGE_TO_ENTITY = [
        'game' => Game::class,
    ];

    const SIMPLE_PAGE = 1;
    const BLOCK_PAGE = 2;
    const MEDIA_PAGE = 3;

    protected $fillable = [
        'name',
        'description',
        'path',
        'type',
        'entity_type',
        'entity_id',
    ];
}
