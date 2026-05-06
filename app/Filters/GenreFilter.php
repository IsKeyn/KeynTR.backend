<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class GenreFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Genre';
    private const TABLE_NAME = 'genres';
}
