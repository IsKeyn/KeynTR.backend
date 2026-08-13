<?php

namespace App\Services\SiteGame;

use App\Models\Game\SaveState;
use Symfony\Component\HttpFoundation\Response;

class SaveStateService
{
    public static function get($playerId, $entityType, $entityId)
    {
        if (!$playerId) {
            abort(Response::HTTP_BAD_REQUEST, __('siteGame.player_id_not_received'));
        }

        if (!$entityType) {
            abort(Response::HTTP_BAD_REQUEST, __('siteGame.entity_type_not_received'));
        }

        if (!$entityId) {
            abort(Response::HTTP_BAD_REQUEST, __('siteGame.entity_id_not_received'));
        }

        return SaveState::query()
            ->findByPlayer($playerId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();
    }
}
