<?php
namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class VersionFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Version';
    private const TABLE_NAME = 'versions';
}
