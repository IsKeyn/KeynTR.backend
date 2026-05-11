<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class MenuFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Menu';
    private const TABLE_NAME = 'menus';
}
