<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\BoardGame\Timer;
use App\Models\User\Notification;

class ActionsService
{
    private $conditionData = null;

    private $type = null;
    private $itemElement = null;
    private $statusEffectElement = null;

    private $BoardGamePlayer = BoardGamePlayer::class;
    private $BoardGamePlayerPosition = BoardGamePlayerPosition::class;
    private $notification = Notification::class;
    private $BoardGameInventory = BoardGameInventory::class;
    private $statusEffect = StatusEffect::class;
    private $PlayerStatusEffect = PlayerStatusEffect::class;

    public function __construct($conditionData, $type, $element)
    {
        $this->conditionData = $conditionData;
        $this->type = $type;

        if ($type === 'item') {
            $this->itemElement = $element;
        } else if ($type === 'statusEffect') {
            $this->statusEffectElement = $element;
        }
    }

    public function activateAction($request, $action)
    {
        switch ($action->type) {
            case 'removePoints':
            case 'addPoints':
                $result = $this->actionsWithPoints(
                    $request,
                    $action,
                );
                break;

            case 'movePlayer':
            case 'pushPlayer':
                $result = $this->actionsWithPosition(
                    $request,
                    $action,
                );
                break;

            case 'removeNegativeItem':
            case 'stealItem':
            case 'changeUserOwner':
            case 'removeItem':
                $result = $this->actionsWithItems(
                    $request,
                    $action,
                );
                break;

            case 'applyStatusEffect':
                $result = $this->activateEffect(
                    $request,
                    $action,
                );
                break;


            case 'addTime':
                $result = $this->actionsWithTime(
                    $request,
                    $action,
                );
                break;

            case 'playerInteractions':
                $result = $this->actionsWithPlayerInteractions(
                    $request,
                    $action,
                );
                break;
            case 'customItem':

                break;
        }

        return $result;
    }

    private function actionsWithPoints($request, $action)
    {
        /* Функция выполняет действия связанные с очками игрока */
        $players = $this->target($request, $action);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) === 'array') {
            foreach ($players as $player) {
                $playerFields = $this->setFieldsWithPoints($player, $action);
                $player->update($playerFields);

                $dontSendNotification = false;

                if (isset($action->sendNotification) && $action->sendNotification === false) {
                    $dontSendNotification = true;
                }

                if (!$dontSendNotification) {
                    if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                        $notificationMessage = $request->additionalParams['message'];
                        $this->createNotification($player, $notificationMessage);
                    }
                }
            }
        }
    }


    private function setFieldsWithPoints($player, $action)
    {
        $value = $this->getValue($action->value);

        if (is_int($value)) {
            if ($action->type === 'addPoints') {
                $playerFields = ['points' => $player->points + $value];
            } elseif ($action->type === 'removePoints') {
                $playerFields = ['points' => $player->points - $value];
            }
        } else {
            if ($value === 'playersAheadCount') {
                /* За каждого игрока впереди */
                $playerPosition = $this->BoardGamePlayerPosition::query()
                    ->where('user_id', $player->user_id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->orderBy('id', 'desc')->first();

                $playersAhead = $this->BoardGamePlayerPosition::query()->where('position', '>', $playerPosition->position)
                    ->where('user_id', '!=', $player->user_id)
                    ->get()->sortByDesc('created_at')->unique('user_id');

                $playersAheadCount = count($playersAhead);

                if ($action->type === 'addPoints') {
                    $playerFields = ['points' => $player->points + $playersAheadCount];
                } elseif ($action->type === 'removePoints') {
                    $playerFields = ['points' => $player->points - $playersAheadCount];
                }
            } else if (str_contains($action->value, 'forEachNear')) {
                /* За каждого игрока рядом */

                $explodedString = explode('_', $action->value);

                $currentPlayerPosition = $this->BoardGamePlayerPosition::query()
                    ->where('user_id', $player->user_id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->orderBy('id', 'desc')->first();

                $nearPlayers = $this->BoardGamePlayerPosition::query()->where('user_id', '!=', $player->user_id)
                    ->get()
                    ->sortByDesc('created_at')
                    ->unique('user_id');

                $count = 0;

                foreach ($nearPlayers as $nearPlayer) {
                    if ($nearPlayer->position >= $currentPlayerPosition->position + (int)$explodedString[1]) {
                        $count++;
                    }

                    if ($nearPlayer->position >= $currentPlayerPosition->position - (int)$explodedString[1]) {
                        $count++;
                    }
                }

                if ($count > 0) {
                    if ($action->type === 'addPoints') {
                        $playerFields = ['points' => $player->points + $count];
                    } elseif ($action->type === 'removePoints') {
                        $playerFields = ['points' => $player->points - $count];
                    }
                } else if ($action->else) {
                    if ($action->else->type === 'addPoints') {
                        $playerFields = ['points' => $player->points + $action->else->value];
                    } elseif ($action->else->type === 'removePoints') {
                        $playerFields = ['points' => $player->points - $action->else->value];
                    }
                }
            }
        }

        if ($this->type === 'item') {
            $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';
        } else if ($this->type === 'statusEffect') {
            $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';
        }

        if ($logMessage) {
            LogService::addLog(
                $this->conditionData['user']->id,
                $this->conditionData['boardGame']->id,
                $logMessage
            );
        }

        return $playerFields;
    }

    private function actionsWithPosition($request, $action)
    {
        $players = $this->target($request, $action);

        foreach ($players as $player) {
            $playerPosition = $this->BoardGamePlayerPosition::query()
                ->where('user_id', $player->user_id)
                ->where('board_game_id', $this->conditionData['boardGame']->id)
                ->orderBy('id', 'desc')->first();

            $playerPositionFields = [
                'user_id' => $player->user_id,
                'board_game_id' => $this->conditionData['boardGame']->id,
                'created_by' => $this->conditionData['user']->id,
            ];

            $value = $this->getValue($action->value);

            /* Проверяем, что игрок не дальше позиции указанной в $action->target */
            if (str_contains($action->target, 'noFurther')) {
                /* Позиция игрока, который использует предмет */
                $usedItemPlayerPosition = $this->BoardGamePlayerPosition::query()
                    ->where('user_id', $this->conditionData['user']->id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($usedItemPlayerPosition->position > $playerPosition->position) {
                    $playerPositionFields['position'] = $this->checkPosition($playerPosition->position - $value);
                } else if ($usedItemPlayerPosition->position < $playerPosition->position) {
                    $playerPositionFields['position'] = $this->checkPosition($playerPosition->position + $value);
                }
            } else if (isset($action->direction)) {
                if ($action->direction === 'forward') {
                    $playerPositionFields['position'] = $this->checkPosition($playerPosition->position + $value);
                } elseif ($action->direction === 'back') {
                    $playerPositionFields['position'] = $this->checkPosition($playerPosition->position - $value);
                }
            }

            if ($this->type === 'item') {
                $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $playerPosition->position . ' - ' . $playerPositionFields['position'] . ')';
            } else if ($this->type === 'statusEffect') {
                $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->points . ' - ' . $playerPositionFields["points"] . ')';
            }

            if ($logMessage) {
                LogService::addLog(
                    $this->conditionData['user']->id,
                    $this->conditionData['boardGame']->id,
                    $logMessage
                );
            }

            $this->BoardGamePlayerPosition::create($playerPositionFields);

            $dontSendNotification = false;

            if (isset($action->sendNotification) && $action->sendNotification === false) {
                $dontSendNotification = true;
            }

            if (!$dontSendNotification) {
                if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                    $notificationMessage = $request->additionalParams['message'];
                    $this->createNotification($player, $notificationMessage);
                }
            }
        }
    }

    private function checkPosition($position)
    {
        if ($position < 0) {
            return 0;
        }

        if ($boardGameType = $this->conditionData['boardGame']->settings()->where('code', '=', 'board_type')->value('value')) {
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

    private function actionsWithItems($request, $action)
    {
        /* Удаление предмета у всех игроков */
        if ($action->type === 'removeItem' && $action->target === 'all' && $action->itemId) {
            // TODO переделать, должен брать не привязанный предмет ид а оригинальный
            $items = $this->BoardGameInventory->where('board_game_item_id', $action->itemId);

            $arUserIds = [];

            foreach ($items->get() as $item) {
                if ($item->user_id !== $this->conditionData['user']->id && !in_array($item->user_id, $arUserIds))  {
                    $fields = [
                        'user_id' => $item->user_id,
                        'created_by' => $this->conditionData['user']->id,
                        'message' => $this->conditionData['user']->name . ' использовал предмет "' . $item->item->name . '"',
                    ];

                    $this->notification->create($fields);

                    $arUserIds[] = $item->user_id;
                }

                $items->delete();
            }
        } else {
            $players = $this->target($request, $action);

            foreach ($players as $player) {
                if (isset($request->additionalParams['item']) && $request->additionalParams['item']) {
                    $inventoryItem = $this->BoardGameInventory
                        ->where('user_id', $player->user_id)
                        ->where('id', $request->additionalParams['item'])
                        ->where('has_used', false)
                        ->first();

                    if ($inventoryItem) {
                        switch ($action->type) {
                            case 'removeNegativeItem':
                                if ($inventoryItem->item->type === 1) {
                                    $inventoryItem->delete();

                                    $dontSendNotification = false;

                                    if (isset($action->sendNotification) && $action->sendNotification === false) {
                                        $dontSendNotification = true;
                                    }

                                    if (!$dontSendNotification) {
                                        if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                            $notificationMessage = $request->additionalParams['message'];
                                            $this->createNotification($player, $notificationMessage);
                                        }
                                    }
                                } else {
                                    return $this->error('Вы пытаетесь удалить предмет, который не является предметом с дебафом');
                                }
                                break;
                            case 'stealItem':
                                $playerFields = [
                                    'user_id' => $this->conditionData['user']->id,
                                ];

                                $inventoryItem->update($playerFields);

                                $dontSendNotification = false;

                                if (isset($action->sendNotification) && $action->sendNotification === false) {
                                    $dontSendNotification = true;
                                }

                                if (!$dontSendNotification) {
                                    if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                        $notificationMessage = $request->additionalParams['message'];
                                        $this->createNotification($player, $notificationMessage);
                                    }
                                }
                                break;
                            case 'changeUserOwner':
                                if (
                                    isset($request->additionalParams['secondPlayer'])
                                    && $request->additionalParams['secondPlayer']
                                ) {
                                    $secondPlayer = $this->BoardGamePlayer->where('id', $request->additionalParams['secondPlayer'])->first();

                                    if ($secondPlayer) {
                                        $playerFields = [
                                            'user_id' => $secondPlayer->user_id,
                                        ];

                                        $inventoryItem->update($playerFields);

                                        $dontSendNotification = false;

                                        if (isset($action->sendNotification) && $action->sendNotification === false) {
                                            $dontSendNotification = true;
                                        }

                                        if (!$dontSendNotification) {
                                            if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                                $notificationMessage = $request->additionalParams['message'];
                                                $this->createNotification($player, $notificationMessage);
                                                $this->createNotification($secondPlayer, $notificationMessage);
                                            }
                                        }
                                    } else {
                                        return $this->error('Второй игрок не найден');
                                    }
                                }
                                break;
                        }
                    } else {
                        return $this->error('Предмета не существует');
                    }
                }
            }
        }
    }

    private function actionsWithTime($request, $action)
    {
        $timer = Timer::query()
            ->where('user_id', $this->conditionData['user']->id)
            ->where('board_game_id', $this->conditionData['boardGame']->id)
            ->where('slug','main')
            ->where('active', true)
            ->orderBy('id', 'desc')->first();

        if ($timer) {
            if ($action->type === 'addTime') {
                $fields = [
                    'limit' => $timer->limit + $action->value,
                ];

                return $timer->update($fields);
            }
        } else {
            $boardGame = BoardGame::query()->where('id', $this->conditionData['boardGame']->id)->first();

            $timerFields = [
                'user_id' => $this->conditionData['user']->id,
                'board_game_id' => $this->conditionData['boardGame']->id,
                'name' => $boardGame->name,
                'limit' => 100*60*60 + $action->value,
                'slug' => 'main',
                'created_by' => $this->conditionData['user']->id,
            ];

            Timer::create($timerFields);
        }
    }

    private function actionsWithPlayerInteractions($request, $action)
    {
        if ($action->value) {
            $players = $this->target($request, $action);

            foreach ($players as $player) {
                switch ($action->value) {
                    case 'switchGame':
                        $playerCurrentGame = PlayerGame::where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('user_id', $player->user_id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($playerCurrentGame) {
                            $fields = [
                                'type' => $action->value,
                                'status' => PlayerInteractions::STATUS_ACTIVE,
                                'board_game_id' => $this->conditionData['boardGame']->id,
                                'created_by' => $this->conditionData['user']->id,
                                'with_player' => $player->user_id,
                                'entity_id' => $this->itemElement->id,
                                'entity_type' => $this->itemElement->model,
                                'active' => true,
                            ];

                            if (PlayerInteractions::create($fields)) {
                                $dontSendNotification = false;

                                if (isset($action->sendNotification) && $action->sendNotification === false) {
                                    $dontSendNotification = true;
                                }

                                if (!$dontSendNotification) {
                                    if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                        $notificationMessage = $request->additionalParams['message'];
                                        $this->createNotification($player, $notificationMessage);
                                    }
                                }

                                return 'Ваш запрос успешно отправлен игроку';

                                // TODO если игрок изменил игру или рерольнул или прошел или обменял с другим игроком, то возвращать предмет и отзывать запрос
                            } else {
                                return $this->error('Ошибка создания взаимодействия игроков');
                            }
                        } else {
                            return $this->error('У данного игрока отсуствует текущая игра');
                        }
                }
            }
        } else {
            return $this->error('Отсутствует тип взаимодействия $action->value');
        }
    }

    private function activateEffect($request, $action)
    {
        /* Функция выполняет действия связанные с эффектами игрока */
        if ($action->value) {
            $statusEffectObj = $this->statusEffect->where('slug', $action->value)->first();

            if ($statusEffectObj) {
                $players = $this->target($request, $action);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        $PlayerStatusEffectFields = [
                            'user_id' => $player->user_id,
                            'board_game_id' => $statusEffectObj->board_game_id,
                            'status_effect_id' => $statusEffectObj->id,
                            'created_by' => $this->conditionData['user']->id,
                        ];

                        $this->PlayerStatusEffect->create($PlayerStatusEffectFields);

                        $dontSendNotification = false;

                        if (isset($action->sendNotification) && $action->sendNotification === false) {
                            $dontSendNotification = true;
                        }

                        if (!$dontSendNotification) {
                            if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                $notificationMessage = $request->additionalParams['message'];
                                $this->createNotification($player, $notificationMessage);
                            }
                        }
                    }
                }
            } else {
                return $this->error('Действие отсутствует');
            }
        } else {
            return $this->error('Действие отсутствует');
        }
    }

    private function getValue($value)
    {
        /*
         * Фунция возращает значение value
         * value может приходит как массив так и строка или число
         * Массив приходит для значений, когда value должно быть случаным в определенном диапозоне
         * в этом случае $value[0] === 'rand', а $value[1] и $value[2] - это начальное и конечное значение случайного диапозона
         */

        if (is_array($value)) {
            if (isset($value[0]) && $value[0] === 'rand' && isset($value[1]) && isset($value[2])) {
                return mt_rand($value[1], $value[2]);
            }
        } else {
            return $value;
        }
    }

    private function target($request, $action)
    {
        $players = [];

        /* Функция, которое определяет, на кого действует предмет */
        switch ($action->target) {
            case 'current':
                $players[] = $this->BoardGamePlayer::query()
                    ->where('user_id', $this->conditionData['user']->id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->first();
                break;

            case 'other':
            case 'nearestPlayer':
            case 'fromTo':
            case str_contains($action->target, 'noFurther'):
                /* TODO ближащий игрок выбрается на фронте, проверять его тут на беке */
                if (isset($request->additionalParams['player']) && $request->additionalParams['player']) {
                    $players[] = $this->BoardGamePlayer::query()
                        ->where('id', $request->additionalParams['player'])
                        ->where('board_game_id', $this->conditionData['boardGame']->id)
                        ->first();
                } else {
                    return ['error' => 'Игрок не выбран'];
                }
                break;

            case 'allExpectMe':
                $playersSelection = $this->BoardGamePlayer::query()
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('user_id', '!=', $this->conditionData['user']->id)
                    ->get();

                foreach ($playersSelection as $player) {
                    $players[] = $player;
                }
                break;

            case 'positionLeader':
                $playersWithPositions = $this->BoardGamePlayerPosition::all()->sortByDesc('created_at')->unique('user_id');

                $playersByPositions = [];

                foreach ($playersWithPositions as $playerWithPosition) {
                    $playersByPositions[$playerWithPosition->position][] = $playerWithPosition;
                }

                foreach ($playersByPositions[max(array_keys($playersByPositions))] as $playerByPosition) {
                    $players[] = $this->BoardGamePlayer::query()
                        ->where('user_id', $playerByPosition->user_id)
                        ->where('board_game_id', $this->conditionData['boardGame']->id)
                        ->first();
                }
                break;
        }

        return $players;
    }

    public function createNotification($player, $message) {
        if (
            isset($player) && $player
            && $message
            && $player->user_id !== $this->conditionData['user']->id
        ) {
            $fields = [
                'user_id' => $player->user_id,
                'created_by' => $this->conditionData['user']->id,
                'message' => $message,
            ];

            $this->notification::create($fields);
        }
    }

    private function error($message)
    {
        /* Функция возврата ошибок действий с предметами */
        return ['error' => $message];
//        return response()->json(['error' => $message])->setStatusCode(Response::HTTP_OK);
    }
}
