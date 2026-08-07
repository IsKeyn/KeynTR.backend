<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Entity\EntityService;
use Symfony\Component\HttpFoundation\Response;

class BgPlayerPositionService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGamePlayerPosition::class,
            BoardGamePlayerPosition::CACHE_SERVICE,
            BoardGamePlayerPosition::DETAIL_RESOURCE,
            $id,
            [
                'tags',
                'additionalFields',
                'media',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }

    /**
     * Устанавливает все ячейки игрового поля для данного игрока, как не использованные
     *
     * @param $player
     * @return int
     */
    public function resetBoardCellEffects($player)
    {
        if (!$player) {
            abort(Response::HTTP_BAD_REQUEST, __('boardGame.player.player_not_received'));
        }

        $result = BoardGamePlayerPosition::query()
            ->where('bg_player_id', $player->id)
            ->update([
                'has_use_effect' => false,
            ]);

        $boardGameCacheService = app(BoardGameCacheService::class);
        $boardGameCacheService->clearDetailCacheBySlug($player->boardGame->slug);

        return $result;
    }

    /**
     * Устанавливаем позицию игрока
     *
     * @param array $conditionData
     * @param int $position
     * @return array
     */
    public function setPlayerPosition(
        array $conditionData,
        int $position
    )
    {
        return BoardService::setPosition(
            [
                'position' => $position,
                'player' => $conditionData['player'],
            ],
            $conditionData,
            false,
            false
        );
    }
}
