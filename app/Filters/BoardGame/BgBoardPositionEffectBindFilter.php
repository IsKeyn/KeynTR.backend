<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardPositionEffectsBind;

class BgBoardPositionEffectBindFilter
{
    use HasFilters;

    private const MODEL = BoardPositionEffectsBind::class;
    private const TABLE_NAME = BoardPositionEffectsBind::TABLE_NAME;
}
