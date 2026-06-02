<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Tag;

class TagFilter
{
    use HasFilters;

    private const MODEL = Tag::class;
    private const TABLE_NAME = Tag::TABLE_NAME;
}
