<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Models\BoardGame\BoardGame;
use App\Services\BoardGame\StatusEffectService;
use Illuminate\Http\Request;

class PlayerStatusEffectController extends Controller
{
    public function use(Request $request)
    {
        $statusEffectService = new StatusEffectService();
        return $statusEffectService->useStatusEffect($request);
    }

    /**
     * Список доступных в данном ивенте Статус Эффектов
     *
     * @param String $slug
     * @return mixed
     */
    public function getList(
        String $slug,
        BoardGame $BoardGame
    )
    {
        $bgId = $BoardGame->findBySlug($slug)->value('id');
        return StatusEffectService::statusEffectsInBoardGame($bgId);
    }
}
