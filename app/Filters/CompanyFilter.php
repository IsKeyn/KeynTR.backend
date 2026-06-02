<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;

class CompanyFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Company';
    private const TABLE_NAME = 'companies';
}
