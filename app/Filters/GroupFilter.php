<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Group;

class GroupFilter
{
    use HasFilters;

    private const MODEL = Group::class;
    private const TABLE_NAME = Group::TABLE_NAME;
}
