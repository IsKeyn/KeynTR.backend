<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class RoleFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Role';
    private const TABLE_NAME = 'roles';
}
