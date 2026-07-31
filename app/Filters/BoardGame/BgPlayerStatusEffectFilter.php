<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\PlayerStatusEffect;

class BgPlayerStatusEffectFilter
{
    use HasFilters;

    private const MODEL = PlayerStatusEffect::class;
    private const TABLE_NAME = PlayerStatusEffect::TABLE_NAME;
}
