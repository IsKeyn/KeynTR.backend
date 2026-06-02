<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Movie;

class MovieFilter
{
    use HasFilters;

    private const MODEL = Movie::class;
    private const TABLE_NAME = Movie::TABLE_NAME;
}
