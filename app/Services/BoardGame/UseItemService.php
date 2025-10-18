<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\BoardGame\Timer;
use App\Models\User\Notification;
use Illuminate\Http\Request;

class UseItemService
{
    public $item = null;
    public $conditionData = [];

    public function useItem(
        Request $request,
        BoardGamePlayer $BoardGamePlayer,
        StatusEffect $statusEffect,
        PlayerStatusEffect $PlayerStatusEffect,
        BoardGamePlayerPosition $BoardGamePlayerPosition,
        ItemBind $BoardGameItem,
        BoardGameInventory $BoardGameInventory,
        Notification $notification
    ) {
        $this->conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($this->conditionData['status']) && $this->conditionData['status'] === 'error') {
            return $this->conditionData;
        } else {
            $result = null;

            if ($request->id) {
                /* Получаем информацию из инветаря пользователя о предмете, предмет должен участвовать в текущей настольной игре */
                $usedInventoryItem = $BoardGameInventory
                    ->where('id', $request->id)
                    ->where('user_id', $this->conditionData['user']->id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('has_used', false)
                    ->first();

                if ($usedInventoryItem && $usedInventoryItem->board_game_item_id) {
                    /* Получаем сущность самого предмета с его данными */
                    $this->item = $BoardGameItem->where('id', $usedInventoryItem->board_game_item_id)->first();

                    /* Предмет должен иметь JSON действий, если его нет, то предмет предназначен для "ручного" использования */
                    if ($this->item->item->actions) {
                        $actionService = new ActionsService($this->conditionData, 'item', $this->item);

                        foreach (json_decode($this->item->item->actions) as $action) {
                            if (isset($action->type) && $action->type) {
                                if ($action->type === 'customItem') {
                                    $result = $this->customItem(
                                        $request,
                                        $action,
                                        $this->conditionData['user'],
                                        $BoardGamePlayer,
                                        $BoardGamePlayerPosition,
                                        $notification,
                                    );
                                } else {
                                    $result = $actionService->activateAction($request, $action);
                                }

//                                switch ($action->type) {
//                                    case 'removePoints':
//                                    case 'addPoints':
//                                        $result = $this->actionsWithPoints(
//                                            $request,
//                                            $action,
//                                            $this->conditionData['user'],
//                                            $BoardGamePlayer,
//                                            $BoardGamePlayerPosition,
//                                            $notification
//                                        );
//                                        break;
//
//                                    case 'movePlayer':
//                                    case 'pushPlayer':
//                                        $result = $this->actionsWithPosition(
//                                            $request,
//                                            $this->conditionData['user'],
//                                            $action,
//                                            $BoardGamePlayer,
//                                            $BoardGamePlayerPosition,
//                                            $notification
//                                        );
//                                        break;
//
//                                    case 'removeNegativeItem':
//                                    case 'stealItem':
//                                    case 'changeUserOwner':
//                                    case 'removeItem':
//                                        $result = $this->actionsWithItems(
//                                            $request,
//                                            $this->conditionData['user'],
//                                            $action,
//                                            $BoardGamePlayer,
//                                            $BoardGameInventory,
//                                            $BoardGamePlayerPosition,
//                                            $notification
//                                        );
//                                        break;
//
//                                    case 'applyStatusEffect':
//                                        $result = $this->activateEffect(
//                                            $request,
//                                            $this->conditionData['user'],
//                                            $action,
//                                            $statusEffect,
//                                            $BoardGamePlayer,
//                                            $PlayerStatusEffect,
//                                            $BoardGamePlayerPosition,
//                                            $notification
//                                        );
//                                        break;
//
//                                    case 'addTime':
//                                        $result = $this->actionsWithTime(
//                                            $request,
//                                            $action,
//                                            $this->conditionData['user']
//                                        );
//                                        break;
//                                    case 'customItem':
//                                        $result = $this->customItem(
//                                            $request,
//                                            $action,
//                                            $this->conditionData['user'],
//                                            $BoardGamePlayer,
//                                            $BoardGamePlayerPosition,
//                                            $notification,
//                                        );
//                                        break;
//
//                                }
                            } elseif (isset($action->target) && $action->target) {
                                $players = $this->target($request, $this->conditionData['user'], $action, $BoardGamePlayer,
                                    $BoardGamePlayerPosition);

                                foreach ($players as $player) {
                                    if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                        $notificationMessage = $request->additionalParams['message'];
                                        $this->createNotification($player, $this->conditionData['user'], $notificationMessage, $notification);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    return $this->error('Предмета нет в инвентаре или он был использован');
                }
            } else {
                return $this->error('Не получен ID предмета инвентаря');
            }

            if ($result['error'] ?? null) {
                return $result;
            }

            $usedItemsFields = ['has_used' => true];

            if ($result) {
                $logMessage = $result;
            } else if (isset($request->additionalParams['logMessage'])) {
                $logMessage = $request->additionalParams['logMessage'];
            } else {
                $logMessage = 'использовал предмет ' . $this->item->item->name;
            }

            LogService::addLog(
                $this->conditionData['user']->id,
                $this->conditionData['boardGame']->id,
                $logMessage
            );

            if ($usedInventoryItem->update($usedItemsFields)) {
                if ($result) {
                    return [
                        'message' => $result,
                    ];
                } else {
                    return true;
                }
            }
        }
    }

    private function target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition)
    {
        $players = [];

        /* Функция, которое определяет, на кого действует предмет */
        switch ($action->target) {
            case 'current':
                $players[] = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $this->conditionData['boardGame']->id)->first();
                break;

            case 'other':
            case 'nearestPlayer':
            case 'fromTo':
            case str_contains($action->target, 'noFurther'):
                /* TODO ближащий игрок выбрается на фронте, проверять его тут на беке */
                if (isset($request->additionalParams['player']) && $request->additionalParams['player']) {
                    $players[] = $BoardGamePlayer->where('id', $request->additionalParams['player'])->where('board_game_id', $this->conditionData['boardGame']->id)->first();
                } else {
                    return $this->error('Игрок не выбран');
                }
                break;

            case 'allExpectMe':
                $playersSelection = $BoardGamePlayer->where('board_game_id', $this->conditionData['boardGame']->id)->where('user_id', '!=', $user->id)->get();

                foreach ($playersSelection as $player) {
                    $players[] = $player;
                }
                break;

            case 'positionLeader':
                $playersWithPositions = $BoardGamePlayerPosition::all()->sortByDesc('created_at')->unique('user_id');

                $playersByPositions = [];

                foreach ($playersWithPositions as $playerWithPosition) {
                    $playersByPositions[$playerWithPosition->position][] = $playerWithPosition;
                }

                foreach ($playersByPositions[max(array_keys($playersByPositions))] as $playerByPosition) {
                    $players[] = $BoardGamePlayer->where('user_id', $playerByPosition->user_id)->where('board_game_id', $this->conditionData['boardGame']->id)->first();
                }
                break;
        }

        return $players;
    }

    private function actionsWithPoints($request, $action, $user, $BoardGamePlayer, $BoardGamePlayerPosition, $notification)
    {
        /* Функция выполняет действия связанные с очками игрока */
       $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) === 'array') {
            foreach ($players as $player) {
                $playerFields = $this->setFieldsWithPoints($request, $player, $action, $BoardGamePlayerPosition);
                $player->update($playerFields);

                $dontSendNotification = false;

                if (isset($action->sendNotification) && $action->sendNotification === false) {
                    $dontSendNotification = true;
                }

                if (!$dontSendNotification) {
                    if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                        $notificationMessage = $request->additionalParams['message'];
                        $this->createNotification($player, $user, $notificationMessage, $notification);
                    }
                }
            }
        }
    }

    private function actionsWithTime($request, $action, $user)
    {
        $timer = Timer::query()
            ->where('user_id', $user->id)
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
                'user_id' => $user->id,
                'board_game_id' => $this->conditionData['boardGame']->id,
                'name' => $boardGame->name,
                'limit' => 100*60*60 + $action->value,
                'slug' => 'main',
                'created_by' => $user->id,
            ];

            Timer::create($timerFields);
        }
    }

    private function setFieldsWithPoints($request, $player, $action, $BoardGamePlayerPosition)
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
                $playerPosition = $BoardGamePlayerPosition
                    ->where('user_id', $player->user_id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->orderBy('id', 'desc')->first();

                $playersAhead = $BoardGamePlayerPosition::where('position', '>', $playerPosition->position)
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

                $currentPlayerPosition = $BoardGamePlayerPosition
                    ->where('user_id', $player->user_id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->orderBy('id', 'desc')->first();

                $nearPlayers = $BoardGamePlayerPosition::where('user_id', '!=', $player->user_id)
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

        $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';

        if ($logMessage) {
            LogService::addLog(
                $this->conditionData['user']->id,
                $this->conditionData['boardGame']->id,
                $logMessage
            );
        }

        return $playerFields;
    }

    private function actionsWithPosition($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition, $notification)
    {
        $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

        foreach ($players as $player) {
            $playerPosition = $BoardGamePlayerPosition
                ->where('user_id', $player->user_id)
                ->where('board_game_id', $this->conditionData['boardGame']->id)
                ->orderBy('id', 'desc')->first();

            $playerPositionFields = [
                'user_id' => $player->user_id,
                'board_game_id' => $this->conditionData['boardGame']->id,
                'created_by' => $user->id,
            ];

            $value = $this->getValue($action->value);

            /* Проверяем, что игрок не дальше позиции указанной в $action->target */
            if (str_contains($action->target, 'noFurther')) {
                /* Позиция игрока, который использует предмет */
                $usedItemPlayerPosition = $BoardGamePlayerPosition
                    ->where('user_id', $user->id)
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

            $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $playerPosition->position . ' - ' . $playerPositionFields['position'] . ')';

            if ($logMessage) {
                LogService::addLog(
                    $this->conditionData['user']->id,
                    $this->conditionData['boardGame']->id,
                    $logMessage
                );
            }

            $BoardGamePlayerPosition->create($playerPositionFields);

            $dontSendNotification = false;

            if (isset($action->sendNotification) && $action->sendNotification === false) {
                $dontSendNotification = true;
            }

            if (!$dontSendNotification) {
                if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                    $notificationMessage = $request->additionalParams['message'];
                    $this->createNotification($player, $user, $notificationMessage, $notification);
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

    private function actionsWithItems($request, $user, $action, $BoardGamePlayer, $BoardGameInventory, $BoardGamePlayerPosition, $notification)
    {
        /* Удаление всех предметов */
        if ($action->type === 'removeItem' && $action->target === 'all' && $action->itemId) {
            // TODO переделать, должен брать не привязанный предмет ид а оригинальный
            $items = $BoardGameInventory->where('board_game_item_id', $action->itemId);

            $arUserIds = [];

            foreach ($items->get() as $item) {
                if ($item->user_id !== $user->id && !in_array($item->user_id, $arUserIds))  {
                    $fields = [
                        'user_id' => $item->user_id,
                        'created_by' => $user->id,
                        'message' => $user->name . ' использовал предмет "' . $item->item->name . '"',
                    ];

                    $notification->create($fields);

                    $arUserIds[] = $item->user_id;
                }

                $items->delete();
            }
        } else {
            $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

            foreach ($players as $player) {
                if (isset($request->additionalParams['item']) && $request->additionalParams['item']) {
                    $inventoryItem = $BoardGameInventory
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
                                            $this->createNotification($player, $this->conditionData['user'], $notificationMessage, $notification);
                                        }
                                    }
                                } else {
                                    return $this->error('Вы пытаетесь удалить предмет, который не является предметом с дебафом');
                                }
                                break;
                            case 'stealItem':
                                $playerFields = [
                                    'user_id' => $user->id,
                                ];

                                $inventoryItem->update($playerFields);

                                $dontSendNotification = false;

                                if (isset($action->sendNotification) && $action->sendNotification === false) {
                                    $dontSendNotification = true;
                                }

                                if (!$dontSendNotification) {
                                    if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                        $notificationMessage = $request->additionalParams['message'];
                                        $this->createNotification($player, $this->conditionData['user'], $notificationMessage, $notification);
                                    }
                                }
                                break;
                            case 'changeUserOwner':
                                if (
                                    isset($request->additionalParams['secondPlayer'])
                                    && $request->additionalParams['secondPlayer']
                                ) {
                                    $secondPlayer = $BoardGamePlayer->where('id', $request->additionalParams['secondPlayer'])->first();

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
                                                $this->createNotification($player, $this->conditionData['user'], $notificationMessage, $notification);
                                                $this->createNotification($secondPlayer, $this->conditionData['user'], $notificationMessage, $notification);
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

    private function activateEffect($request, $user, $action, $statusEffect, $BoardGamePlayer, $PlayerStatusEffect, $BoardGamePlayerPosition, $notification)
    {
        /* Функция выполняет действия связанные с эффектами игрока */
        if ($action->value) {
            $statusEffectObj = $statusEffect->where('slug', $action->value)->first();

            if ($statusEffectObj) {
                $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        $PlayerStatusEffectFields = [
                            'user_id' => $player->user_id,
                            'board_game_id' => $statusEffectObj->board_game_id,
                            'status_effect_id' => $statusEffectObj->id,
                            'created_by' => $user->id,
                        ];

                        $PlayerStatusEffect->create($PlayerStatusEffectFields);

                        $dontSendNotification = false;

                        if (isset($action->sendNotification) && $action->sendNotification === false) {
                            $dontSendNotification = true;
                        }

                        if (!$dontSendNotification) {
                            if (isset($request->additionalParams['message']) && $request->additionalParams['message']) {
                                $notificationMessage = $request->additionalParams['message'];
                                $this->createNotification($player, $this->conditionData['user'], $notificationMessage, $notification);
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

    private function customItem($request, $action, $user, $BoardGamePlayer, $BoardGamePlayerPosition, $notification)
    {
        /* Функция работает со сложными предметами, которые имеют кастомный код активации */
        switch ($action->name) {
            case 'unstable-bomb':
                /*
                 * $action->value[0] - урон игроку на которого применяется 1-10
                 * $action->value[1] - урон при взрыве в руках 1-10
                 * $action->value[2] - вероятность взрыва бомбы в руках 10
                 */

                $message = '';
                $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        if (mt_rand(1, 100) <= $action->value[2] ?? 10) {
                            if (isset($action->value[1])) {
                                $arDamage = explode('-', $action->value[0]);

                                if (isset($arDamage[0]) && isset($arDamage[1])) {
                                    $damage = mt_rand($arDamage[0], $arDamage[1]);
                                    $currentPlayer = $BoardGamePlayer->where('user_id',
                                        $user->id)->where('board_game_id',
                                        $this->conditionData['boardGame']->id)->first();

                                    $logMessage = 'Игрок потерял ' . $damage . ' очков из-за неудачного использования ' . $this->item->item->name . ' (' . $currentPlayer->points;

                                    $playerFields = ['points' => $currentPlayer->points - $damage];
                                    $currentPlayer->update($playerFields);

                                    $logMessage .= ' - ' . $currentPlayer->points . ')';

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', однако ' . $this->item->item->name . ' взовалась в руках игрока и нанесла ему ' . $damage . ' урона';
                                } else {
                                    return $this->error('Не указан формат урона, пример корректного формата "1-10"');
                                }
                            } else {
                                return $this->error('Не указан урон, который должен быть нанесен при взрыве в руках');
                            }
                        } else {
                            if (isset($action->value[0])) {
                                $arDamage = explode('-', $action->value[0]);

                                if (isset($arDamage[0]) && isset($arDamage[1])) {
                                    $damage = mt_rand($arDamage[0], $arDamage[1]);

                                    $logMessage = 'Отнял у игрока ' . ' ' . $player->user->name . ' ' . $damage . ' очков с помощью ' . $this->item->item->name . ' (' . $player->points;

                                    $playerFields = ['points' => $player->points - $damage];
                                    $player->update($playerFields);

                                    $logMessage .= ' - ' . $player->points . ')';

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . '. ' . $this->item->item->name . ' успешно сработала и нанесла ' . $damage . ' урона';
                                } else {
                                    return $this->error('Не указан формат урона, пример корректного формата "10-50"');
                                }
                            } else {
                                return $this->error('Не указан урон, который должен быть нанесен');
                            }
                        }

                        if ($logMessage) {
                            LogService::addLog(
                                $this->conditionData['user']->id,
                                $this->conditionData['boardGame']->id,
                                $logMessage
                            );
                        }

                        $notificationMessage = $message . 'Сообщение от игрока: ' . $request->additionalParams['message'];

                        $dontSendNotification = false;

                        if (isset($action->sendNotification) && $action->sendNotification === false) {
                            $dontSendNotification = true;
                        }

                        if (!$dontSendNotification) {
                            $this->createNotification(
                                $player,
                                $this->conditionData['user'],
                                $notificationMessage,
                                $notification
                            );
                        }

                        return $message;
                    }
                }
                break;
            case 'youre-a-rat':
                /*
                 * $action->value[0] - шанс провалить кражу, если предметов больше 1
                 * $action->value[1] - шанс провалить кражу, если предмет только 1
                 * $action->value[2] - штрав в количестве очков за провал кражи
                 */

                $message = '';
                $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        if ($player->inventory->count() > 0) {
                            $logMessage = null;

                            if ($player->inventory->count() === 1) {
                                if (mt_rand(1, 100) <= $action->value[1] ?? 50) {
                                    $inventoryItem = $player->inventory->first;

                                    $playerFields = [
                                        'user_id' => $user->id,
                                    ];

                                    $inventoryItem->update($playerFields);

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', кража удалась, игрок украл ' . $inventoryItem->item->item->name;
                                } else {
                                    if (isset($action->value[2])) {
                                        $currentPlayer = $BoardGamePlayer
                                            ->where('user_id', $user->id)
                                            ->where('board_game_id', $this->conditionData['boardGame']->id)
                                            ->first();

                                        $logMessage = 'Игрок потерял ' . $action->value[2] . ' очков из-за неудачного использования ' . $this->item->item->name . ' (' . $currentPlayer->points;

                                        $playerFields = ['points' => $currentPlayer->points - $action->value[2]];
                                        $currentPlayer->update($playerFields);

                                        $logMessage .= ' - ' . $currentPlayer->points . ')';

                                        $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', однако кража не удалась и игрок заплатил штраф ' . $action->value[2] . ' очков';
                                    } else {
                                        return $this->error('Не указано количество очков за отнимаемых при провале кражи');
                                    }
                                }
                            } else {
                                if (mt_rand(1, 100) <= $action->value[0] ?? 20) {
                                    $inventoryItem = $player->inventory->random();

                                    $playerFields = [
                                        'user_id' => $user->id,
                                    ];

                                    $inventoryItem->update($playerFields);

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', кража удалась, игрок украл ' . $inventoryItem->item->item->name;
                                } else {
                                    if (isset($action->value[2])) {
                                        $currentPlayer = $BoardGamePlayer
                                            ->where('user_id', $user->id)
                                            ->where('board_game_id', $this->conditionData['boardGame']->id)
                                            ->first();

                                        $logMessage = 'Игрок потерял ' . $action->value[2] . ' очков из-за неудачного использования ' . $this->item->item->name . ' (' . $currentPlayer->points;

                                        $playerFields = ['points' => $currentPlayer->points - $action->value[2]];
                                        $currentPlayer->update($playerFields);

                                        $logMessage .= ' - ' . $currentPlayer->points . ')';

                                        $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', однако кража не удалась и игрок заплатил штраф ' . $action->value[2] . ' очков';
                                    } else {
                                        return $this->error('Не указано количество очков за отнимаемых при провале кражи');
                                    }
                                }
                            }

                            if ($logMessage) {
                                LogService::addLog(
                                    $this->conditionData['user']->id,
                                    $this->conditionData['boardGame']->id,
                                    $logMessage
                                );
                            }

                            $notificationMessage = $message . 'Сообщение от игрока: ' . $request->additionalParams['message'];

                            $dontSendNotification = false;

                            if (isset($action->sendNotification) && $action->sendNotification === false) {
                                $dontSendNotification = true;
                            }

                            if (!$dontSendNotification) {
                                $this->createNotification(
                                    $player,
                                    $this->conditionData['user'],
                                    $notificationMessage,
                                    $notification
                                );
                            }

                            return $message;
                        } else {
                            return $this->error('Не примениму к этому игроку, у него нет предметов');
                        }
                    }
                }
                break;
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

    private function error($message)
    {
        /* Функция возврата ошибок действий с предметами */
        return ['error' => $message];
//        return response()->json(['error' => $message])->setStatusCode(Response::HTTP_OK);
    }

    public function createNotification($player, $user, $message, $notification) {
        if (
            isset($player) && $player
            && $message
            && $player->user_id !== $user->id
        ) {
            $fields = [
                'user_id' => $player->user_id,
                'created_by' => $user->id,
                'message' => $message,
            ];

            $notification->create($fields);
        }
    }
}
