<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardPositionEffect;

class BgBoardPositionEffectFilter
{
    use HasFilters;

    private const MODEL = BoardPositionEffect::class;
    private const TABLE_NAME = BoardPositionEffect::TABLE_NAME;
}
