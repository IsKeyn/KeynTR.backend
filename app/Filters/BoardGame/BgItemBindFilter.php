<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\ItemBind;

class BgItemBindFilter
{
    use HasFilters;

    private const MODEL = ItemBind::class;
    private const TABLE_NAME = ItemBind::TABLE_NAME;
}
