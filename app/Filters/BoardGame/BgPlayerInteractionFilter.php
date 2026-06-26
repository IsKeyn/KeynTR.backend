<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\PlayerInteractions;

class BgPlayerInteractionFilter
{
    use HasFilters;

    private const MODEL = PlayerInteractions::class;
    private const TABLE_NAME = PlayerInteractions::TABLE_NAME;
}
