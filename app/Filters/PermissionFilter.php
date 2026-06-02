<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class PermissionFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Permission';
    private const TABLE_NAME = 'permissions';
}
