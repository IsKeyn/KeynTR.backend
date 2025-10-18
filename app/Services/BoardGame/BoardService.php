<?php
namespace App\Services\BoardGame;

use App\Models\BoardGame\Board;
use App\Models\BoardGame\PlayerInteractions;
use App\Services\ErrorService;
use App\Models\BoardGame\BoardGamePlayerPosition;

class BoardService
{
    public static function setPosition($params, $setLogs = true, $useStepCount = true)
    {
        if (
            $params
            && $params['boardGame']
            && $params['player']
        ) {
            if ($useStepCount && $params['player']->step_count <= 0) {
                return ErrorService::message('Нет доступных ходов');
            }

            if ($params['position']) {
                $position = $params['position'];
            } elseif ($params['type'] && $params['count']) {
                $oldPosition = BoardGamePlayerPosition::query()
                    ->where('board_game_id', $params['boardGame']->id)
                    ->where('user_id', $params['player']->user_id)
                    ->orderBy('id', 'desc')->first();

                if ($params['type'] === 'forward') {
                    $position = $oldPosition->position + $params['count'];
                }

                if ($params['type'] === 'back') {
                    $position = $oldPosition->position - $params['count'];
                }
            }

            $position = self::checkPosition($position, $params['boardGame']);

            if ($position !== $oldPosition->position) {
                $newPosition = [
                    'position' => $position,
                    'board_game_id' => $params['boardGame']->id,
                    'user_id' => $params['player']->user_id,
                    'created_by' => $params['player']->user_id,
                ];

                if ($entry = BoardGamePlayerPosition::create($newPosition)) {
                    if ($setLogs) {
                        /* Запись логов */
                        $logMessage = "перешел с $oldPosition->position ячейки на ячейку $entry->position";

                        LogService::addLog(
                            $params['player']->user_id,
                            $params['boardGame']->id,
                            $logMessage
                        );
                    }

                    if ($useStepCount) {
                        $params['player']->step_count--;
                        $params['player']->update();
                    }

                    $boardInteraction = PlayerInteractions::query()
                        ->findByBoardGame($params['boardGame']->id)
                        ->where('created_by', $params['player']->user_id)
                        ->where('type', 'battleForPoints')
                        ->active()
                        ->get();

                    if ($boardInteraction) {
                        $interactionsService = new InteractionsService();

                        foreach ($boardInteraction as $interaction) {
                            $interactionsService->init($params['boardGame']->slug, $interaction->id, 'systemRefuse');
                        }
                    }

                    // TODO активация эффекта, если эффект автоматический, например смена позиции, добавление очков и т.д.

                    return $entry;
                }
            }
        }
    }

    public static function checkPosition($position, $boardGame)
    {
        if ($position < 1) {
            return 1;
        }

        if ($boardGameType = $boardGame->settings()->where('code', '=', 'board_type')->value('value')) {
            if ($board = Board::query()->where('slug', '=', $boardGameType)->first()) {
                $maxIndex = 0;

                foreach (json_decode($board->columns) as $row) {
                    foreach ($row->cols as $col) {
                        if (isset($col->index) && $maxIndex < $col->index) {
                            $maxIndex = $col->index;
                        }
                    }
                }

                if ($position > $maxIndex) {
                    return $maxIndex;
                }
            }
        }

        return $position;
    }

    public static function setUsePositionEffect($userId, $boardGameId, $position)
    {
        $positionRow = BoardGamePlayerPosition::query()
            ->findByBoardGame($boardGameId)->findByUserId($userId)
            ->where('position', $position)->first();

        if ($positionRow) {
            $positionRow->has_use_effect = true;
            $positionRow->save();
        }
    }
}
