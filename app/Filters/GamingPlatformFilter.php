<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class GamingPlatformFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\GamingPlatform';
    private const TABLE_NAME = 'gaming_platforms';
}
