<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class SeriesFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Series';
    private const TABLE_NAME = 'series';
}
