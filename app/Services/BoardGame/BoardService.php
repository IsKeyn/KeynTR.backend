<?php
namespace App\Services\BoardGame;

use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\PlayerInteractions;
use App\Services\Entity\EntityService;
use App\Services\ErrorService;
use App\Models\BoardGame\BoardGamePlayerPosition;

class BoardService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Board::class,
            Board::CACHE_SERVICE,
            Board::DETAIL_RESOURCE,
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

    public static function setPosition(
        $params,
        $conditionData,
        $setLogs = true,
        $useStepCount = true // Boolean: Учитывать количество доступных ходов
    )
    {
        if (
            $params
            && $conditionData
            && isset($params['player'])
            && $params['player']
        ) {
            if ($useStepCount && $params['player']->step_count <= 0) {
                return ErrorService::message('Нет доступных ходов');
            }

            // Получаем текущую позицию игрока
            $oldPosition = BoardGamePlayerPosition::query()
                ->where('board_game_id', $conditionData['boardGame']->id)
                ->where('user_id', $params['player']->user_id)
                ->orderBy('id', 'desc')->first();

            // Формируем новую позицию игрока
            if (isset($params['position'])) {
                $position = $params['position'];
            } elseif (isset($params['type']) && isset($params['count'])) {
                if ($params['type'] === 'forward') {
                    $position = $oldPosition->position + $params['count'];
                }

                if ($params['type'] === 'back') {
                    $position = $oldPosition->position - $params['count'];
                }
            }

            // Проводим проверку позиции и производим смещение, если позиция не доступна
            $position = self::checkPosition($position, $conditionData['boardGame']);

            // Если новая позиция не равна старой, то записываем новую позицию в БД
            if ($position !== $oldPosition->position) {
                $newPosition = [
                    'position' => $position,
                    'board_game_id' => $conditionData['boardGame']->id,
                    'user_id' => $params['player']->user_id,
                    'created_by' => $conditionData['user']->id,
                ];

                if ($entry = BoardGamePlayerPosition::create($newPosition)) {
                    // Записываем логи
                    if ($setLogs) {
                        $logMessage = "перешел с $oldPosition->position ячейки на ячейку $entry->position";

                        LogService::addLog(
                            $params['player']->user_id,
                            $conditionData['boardGame']->id,
                            $logMessage
                        );
                    }

                    if ($useStepCount) {
                        $params['player']->step_count--;
                        $params['player']->update();
                    }

                    // Отключаем взаимодействия, ячейки игрового поля, которую игрок покинул
                    $boardInteraction = PlayerInteractions::query()
                        ->findByBoardGame($conditionData['boardGame']->id)
                        ->where('created_by', $params['player']->user_id)
                        ->where('type', 'battleForPoints')
                        ->active()
                        ->get();

                    if ($boardInteraction) {
                        $interactionsService = new InteractionsService();

                        foreach ($boardInteraction as $interaction) {
                            $interactionsService->init($conditionData['boardGame']->slug, $interaction->id, 'systemRefuse');
                        }
                    }

                    // Активация эффекта, если эффект автоматический, например смена позиции, добавление очков и т.д.
                    $boardPositionEffectBinds = BoardPositionEffectsBind::query()
                        ->findByBoardGame($conditionData['boardGame']->id)
                        ->where('position', $position)
                        ->active()
                        ->get();

                    $data = (object)['additionalParams' => ['player' => $params['player']->id]];

                    foreach ($boardPositionEffectBinds as $boardPositionEffectBind) {
                        self::activateCellEffect(
                            $boardPositionEffectBind,
                            $entry,
                            $conditionData,
                            $data,
                            true,
                            $params['player']->user_id
                        );
                    }

                    $finalPosition = BoardGamePlayerPosition::query()
                        ->findByBoardGame($conditionData['boardGame']->id)
                        ->findByUserId($params['player']->user_id)
                        ->orderByDesc('id')
                        ->first();

                    return ['firstPosition' => $entry, 'finalPosition' => $finalPosition];
                }
            }
        }
    }

    public static function activateCellEffect(
        $boardPositionEffectBind,
        $position,
        $conditionData,
        $data = null,
        $onlyAutoUse = false,
        $userId = null
    )
    {
        $userId = $userId ? $userId : $conditionData['user']->id;

        $hasUsePosition = BoardGamePlayerPosition::query()
            ->findByBoardGame($conditionData['boardGame']->id)
            ->findByUserId($userId)
            ->where('position', $position->position)
            ->where('has_use_effect', true)
            ->exists();

        if (!$hasUsePosition) {
            if ($boardPositionEffectBind && $boardPositionEffectBind->boardPositionEffect) {
                /* Эффект должен иметь JSON действий */
                if ($boardPositionEffectBind->boardPositionEffect->actions) {
                    $actionService = new ActionsService(
                        $conditionData,
                        'positionEffect',
                        $boardPositionEffectBind->boardPositionEffect
                    );

                    $result = null;

                    foreach (json_decode($boardPositionEffectBind->boardPositionEffect->actions) as $action) {
                        $activateEffect = true;

                        if ($onlyAutoUse && (!isset($action->autoUse) || !$action->autoUse)) {
                            $activateEffect = false;
                        }

                        if ($activateEffect) {
                            $result = $actionService->activateAction($data, $action, $userId);

                            if ($result
                                && (
                                    (isset($data) && (($data->type ?? null) === 'fightWithBoss-win'))
                                    || (isset($action->autoUse) && $action->autoUse)
                                )
                            ) {
                                BoardService::setUsePositionEffect(
                                    $userId,
                                    $conditionData['boardGame']->id,
                                    $boardPositionEffectBind->position
                                );
                            }
                        }
                    }

                    return $result;
                } else {
                    return ErrorService::message('Не возможно применить, не найден JSON действий');
                }
            } else {
                return ErrorService::message('Не найден эффект позиции или привязки эффекта к доске');
            }
        } else {
            return ErrorService::message('Вы уже использовали эффект данной ячейки');
        }
    }

    public static function checkPosition($position, $boardGame)
    {
        if ($position < 1) {
            return 1;
        }

        $maxBoardPosition = self::getMaxBoardPosition($boardGame);

        if ($position > $maxBoardPosition) {
            return $maxBoardPosition;
        }

        return $position;
    }

    public static function getMaxBoardPosition($boardGame)
    {
        $maxBoardPosition = 0;

        if ($boardGameType = $boardGame->settings()->where('code', '=', 'board_type')->value('value')) {
            if ($board = Board::query()->where('slug', '=', $boardGameType)->first()) {
                foreach (json_decode($board->columns) as $row) {
                    foreach ($row->cols as $col) {
                        if (isset($col->index) && $maxBoardPosition < $col->index) {
                            $maxBoardPosition = $col->index;
                        }
                    }
                }
            }
        }

        return $maxBoardPosition;
    }

    public static function setUsePositionEffect($userId, $boardGameId, $position)
    {
        $positionRow = BoardGamePlayerPosition::query()
            ->findByBoardGame($boardGameId)
            ->findByUserId($userId)
            ->where('position', $position)
            ->orderBy('id', 'desc')
            ->first();

        if ($positionRow) {
            $positionRow->has_use_effect = true;
            $positionRow->save();
        }
    }

    public static function fillBoardGame()
    {

    }
}
