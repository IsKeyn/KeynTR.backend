<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\Item;

class BgItemFilter
{
    use HasFilters;

    private const MODEL = Item::class;
    private const TABLE_NAME = Item::TABLE_NAME;
}
