<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Setting;

class SettingFilter
{
    use HasFilters;

    private const MODEL = Setting::class;
    private const TABLE_NAME = Setting::TABLE_NAME;
}
