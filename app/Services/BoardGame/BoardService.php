<?php
namespace App\Services\BoardGame;

use App\Models\BoardGame\Board;
use App\Services\ErrorService;
use App\Models\BoardGame\BoardGamePlayerPosition;

class BoardService
{
    public static function setPosition($params)
    {
        if (
            $params
            && $params['type']
            && $params['count']
            && $params['boardGame']
            && $params['player']
        ) {
            if ($params['player']->step_count > 0) {
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

                $position = self::checkPosition($position, $params['boardGame']);

                if ($position !== $oldPosition->position) {
                    $newPosition = [
                        'position' => $position,
                        'board_game_id' => $params['boardGame']->id,
                        'user_id' => $params['player']->user_id,
                        'created_by' => $params['player']->user_id,
                    ];

                    if ($entry = BoardGamePlayerPosition::create($newPosition)) {
                        /* Запись логов */
                        $logMessage = "перешел с $oldPosition->position ячейки на ячейку $entry->position";

                        LogService::addLog(
                            $params['player']->user_id,
                            $params['boardGame']->id,
                            $logMessage
                        );

                        $params['player']->step_count--;
                        $params['player']->update();

                        return $entry;
                    }
                }
            } else {
                return ErrorService::message('Нет доступных ходов');
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
}
