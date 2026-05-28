<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGameInventory;

class BgInventoryFilter
{
    use HasFilters;

    private const MODEL = BoardGameInventory::class;
    private const TABLE_NAME = BoardGameInventory::TABLE_NAME;
}
