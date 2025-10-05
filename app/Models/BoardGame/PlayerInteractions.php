<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerInteractions extends Model
{
    protected $table = 'bg_player_interactions';

    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'with_player',
        'type',
        'status',
        'created_by',
        'active',
    ];
}
