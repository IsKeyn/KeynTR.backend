<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class UserFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\User';
    private const TABLE_NAME = 'users';
}
