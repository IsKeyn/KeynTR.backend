<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Article;

class ArticleFilter
{
    use HasFilters;

    private const MODEL = Article::class;
    private const TABLE_NAME = Article::TABLE_NAME;
}
