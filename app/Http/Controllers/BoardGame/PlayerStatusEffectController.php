<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Services\BoardGame\StatusEffectService;
use Illuminate\Http\Request;

class PlayerStatusEffectController extends Controller
{
    public function use(Request $request) {
        $statusEffectService = new StatusEffectService();
        return $statusEffectService->useStatusEffect($request);
    }
}
