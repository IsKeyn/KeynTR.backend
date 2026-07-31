<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Services\ErrorService;

class UseItemService
{
    public $item = null;
    public $conditionData = [];
    public $actionService = null;
    public $useResult = null;

    public function __construct($conditionData = null)
    {
        if ($conditionData) {
            $this->conditionData = $conditionData;
        }
    }

    public function useItem($data) {
        if (!$this->conditionData) {
            $this->conditionData = PlayerGameService::checkConditions($data->slug);
        }

        if (isset($this->conditionData['status']) && $this->conditionData['status'] === 'error') {
            return $this->conditionData;
        }

        if (!$data->id) {
            return ErrorService::message('Не получен ID предмета инвентаря');
        }

        $result = null;

        /*
         * Получаем информацию из инветаря пользователя о предмете,
         * предмет должен участвовать в текущей настольной игре
         */
        $usedInventoryItem = BoardGameInventory::query()
            ->where('id', $data->id)
            ->where('user_id', $this->conditionData['user']->id)
            ->where('board_game_id', $this->conditionData['boardGame']->id)
            ->where('has_used', false)
            ->with([
                'item',
                'item.item',
            ])
            ->first();

        if (!$usedInventoryItem || !$usedInventoryItem->board_game_item_id) {
            return ErrorService::message('Предмета нет в инвентаре или он был использован');
        }

        if (!$usedInventoryItem->item) {
            return ErrorService::message('Предмета не найден');
        }

        $this->item = $usedInventoryItem->item;

        /* Предмет должен иметь JSON действий, если его нет, то предмет предназначен для "ручного" использования */
        if ($this->item->item->actions) {
            $this->actionService = new ActionsService($this->conditionData, 'item', $this->item);

            foreach ($this->item->item->actions as $action) {
                $action = (object) $action; // Для корректной работы легаси кода

                if (isset($action->type) && $action->type) {
                    if ($action->type === 'customItem') {
                        $result = $this->customItem($data, $action, $this->conditionData['user']);
                    } else {
                        $result = $this->actionService->activateAction($data, $action);
                    }
                } elseif (isset($action->target) && $action->target) {
                    $players = $this->actionService->target($data, $action);

                    foreach ($players as $player) {
                        $this->actionService->notificationHandler($data, $player, $action);
                    }
                }
            }
        }

        if ($result['error'] ?? null) {
            return $result;
        }

        $usedItemsFields = ['has_used' => true];

        if ($this->useResult) {
            $usedItemsFields['use_result'] = $this->useResult;
        }

        if ($result && isset($result['logMessage']) && is_string($result['logMessage'])) {
            $logMessage = $result;
        } else if (isset($data->additionalParams['logMessage'])) {
            $logMessage = $data->additionalParams['logMessage'];
        } else {
            $logMessage = 'использовал предмет ' . $this->item->item->name;
        }

        LogService::addLog(
            $this->conditionData['user']->id,
            $this->conditionData['boardGame']->id,
            is_string($logMessage) ? $logMessage : $logMessage['logMessage'] ?? null,
            $this->conditionData['player']->id,
        );

        if ($usedInventoryItem->update($usedItemsFields)) {
            if ($result && is_string($result)) {
                return [
                    'message' => $result,
                ];
            } elseif ($result && isset($result['returnMessage']) && is_string($result['returnMessage'])) {
                return [
                    'message' => $result['returnMessage'],
                ];
            } else {
                return true;
            }
        }
    }

    private function customItem($data, $action, $user)
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
                $useResult = null;
                $players = $this->actionService->target($data, $action);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        if (mt_rand(1, 100) <= $action->value[2] ?? 10) {
                            if (isset($action->value[1])) {
                                $arDamage = explode('-', $action->value[1]);

                                if (isset($arDamage[0]) && isset($arDamage[1])) {
                                    $damage = mt_rand($arDamage[0], $arDamage[1]);

                                    $useResult = [
                                        'type' => false,
                                        'value' => $damage,
                                    ];

                                    $currentPlayer = BoardGamePlayer::query()
                                        ->where('user_id', $user->id)
                                        ->where('board_game_id', $this->conditionData['boardGame']->id)
                                        ->first();

                                    $logMessage = 'Игрок потерял ' . $damage . ' очков из-за неудачного использования ' . $this->item->item->name . ' (' . $currentPlayer->points;

                                    $playerFields = ['points' => $currentPlayer->points - $damage];
                                    $currentPlayer->update($playerFields);

                                    $logMessage .= ' - ' . $currentPlayer->points . ')';

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', однако ' . $this->item->item->name . ' взовалась в руках игрока и нанесла ему ' . $damage . ' урона';
                                } else {
                                    return ErrorService::message('Не указан формат урона, пример корректного формата "1-10"');
                                }
                            } else {
                                return ErrorService::message('Не указан урон, который должен быть нанесен при взрыве в руках');
                            }
                        } else {
                            if (isset($action->value[0])) {
                                $arDamage = explode('-', $action->value[0]);

                                if (isset($arDamage[0]) && isset($arDamage[1])) {
                                    $damage = mt_rand($arDamage[0], $arDamage[1]);

                                    $useResult = [
                                        'type' => true,
                                        'value' => $damage,
                                    ];

                                    $logMessage = 'Отнял у игрока ' . ' ' . $player->user->name . ' ' . $damage . ' очков с помощью ' . $this->item->item->name . ' (' . $player->points;

                                    $playerFields = ['points' => $player->points - $damage];
                                    $player->update($playerFields);

                                    $logMessage .= ' - ' . $player->points . ')';

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . '. ' . $this->item->item->name . ' успешно сработала и нанесла ' . $damage . ' урона';
                                } else {
                                    return ErrorService::message('Не указан формат урона, пример корректного формата "10-50"');
                                }
                            } else {
                                return ErrorService::message('Не указан урон, который должен быть нанесен');
                            }
                        }

                        $this->useResult = $useResult;

                        if ($logMessage) {
                            LogService::addLog(
                                $this->conditionData['user']->id,
                                $this->conditionData['boardGame']->id,
                                $logMessage,
                                $this->conditionData['player']->id,
                            );
                        }

                        if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                            $notificationMessage = $message . ' Сообщение от игрока: ' . $data->additionalParams['message'];
                        }

                        $dontSendNotification = false;

                        if (isset($action->sendNotification) && $action->sendNotification === false) {
                            $dontSendNotification = true;
                        }

                        if (!$dontSendNotification) {
                            $this->actionService->createNotification($player, $notificationMessage);
                        }

                        return [
                            'returnMessage' => $message,
                        ];
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
                $useResult = null;
                $players = $this->actionService->target($data, $action);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        if ($player->inventory->where('board_game_id', $this->conditionData['boardGame']->id)->where('has_used', false)->count() > 0) {
                            $logMessage = null;

                            if ($player->inventory->where('board_game_id', $this->conditionData['boardGame']->id)->where('has_used', false)->count() === 1) {
                                if (mt_rand(1, 100) <= $action->value[1] ?? 50) {
                                    $inventoryItem = $player->inventory->where('board_game_id', $this->conditionData['boardGame']->id)->where('has_used', false)->first();

                                    $playerFields = [
                                        'user_id' => $user->id,
                                    ];

                                    $inventoryItem->update($playerFields);

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', кража удалась, игрок украл ' . $inventoryItem->item->item->name;

                                    $useResult = [
                                        'type' => true,
                                        'value' => $inventoryItem->item->item->name,
                                    ];
                                } else {
                                    if (isset($action->value[2])) {
                                        $currentPlayer = BoardGamePlayer::query()
                                            ->where('user_id', $user->id)
                                            ->where('board_game_id', $this->conditionData['boardGame']->id)
                                            ->first();

                                        $logMessage = 'Игрок потерял ' . $action->value[2] . ' очков из-за неудачного использования ' . $this->item->item->name . ' (' . $currentPlayer->points;

                                        $playerFields = ['points' => $currentPlayer->points - $action->value[2]];
                                        $currentPlayer->update($playerFields);

                                        $logMessage .= ' - ' . $currentPlayer->points . ')';

                                        $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', однако кража не удалась и игрок заплатил штраф ' . $action->value[2] . ' очков';

                                        $useResult = [
                                            'type' => false,
                                            'value' => $action->value[2],
                                        ];
                                    } else {
                                        return ErrorService::message('Не указано количество очков за отнимаемых при провале кражи');
                                    }
                                }
                            } else {
                                if (mt_rand(1, 100) > $action->value[0] ?? 20) {
                                    $inventoryItem = $player->inventory->where('board_game_id', $this->conditionData['boardGame']->id)->where('has_used', false)->random();

                                    $playerFields = [
                                        'user_id' => $user->id,
                                    ];

                                    $inventoryItem->update($playerFields);

                                    $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', кража удалась, игрок украл ' . $inventoryItem->item->item->name;

                                    $useResult = [
                                        'type' => true,
                                        'value' => $inventoryItem->item->item->name,
                                    ];
                                } else {
                                    if (isset($action->value[2])) {
                                        $currentPlayer = BoardGamePlayer::query()
                                            ->where('user_id', $user->id)
                                            ->where('board_game_id', $this->conditionData['boardGame']->id)
                                            ->first();

                                        $logMessage = 'Игрок потерял ' . $action->value[2] . ' очков из-за неудачного использования ' . $this->item->item->name . ' (' . $currentPlayer->points;

                                        $playerFields = ['points' => $currentPlayer->points - $action->value[2]];
                                        $currentPlayer->update($playerFields);

                                        $logMessage .= ' - ' . $currentPlayer->points . ')';

                                        $message .= 'Попытался применить ' . $this->item->item->name . ' на игрока ' . $player->user->name . ', однако кража не удалась и игрок заплатил штраф ' . $action->value[2] . ' очков';

                                        $useResult = [
                                            'type' => false,
                                            'value' => $action->value[2],
                                        ];
                                    } else {
                                        return ErrorService::message('Не указано количество очков за отнимаемых при провале кражи');
                                    }
                                }
                            }

                            $this->useResult = $useResult;

                            if ($logMessage) {
                                LogService::addLog(
                                    $this->conditionData['user']->id,
                                    $this->conditionData['boardGame']->id,
                                    $logMessage,
                                    $this->conditionData['player']->id,
                                );
                            }

                            if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                                $notificationMessage = $message . ' Сообщение от игрока: ' . $data->additionalParams['message'];
                            }

                            $dontSendNotification = false;

                            if (isset($action->sendNotification) && $action->sendNotification === false) {
                                $dontSendNotification = true;
                            }

                            if (!$dontSendNotification) {
                                $this->actionService->createNotification($player, $notificationMessage);
                            }

                            return [
                                'returnMessage' => $message,
                            ];
                        } else {
                            return ErrorService::message('Не примениму к этому игроку, у него нет предметов');
                        }
                    }
                }
                break;
            case 'ultra-moshna':
                $message = '';
                $players = $this->actionService->target($data, $action);

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        // Проверяем игру текущего игрока и может ли он её передать
                        $currentUserCurrentGame = PlayerGame::where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('user_id', $this->conditionData['user']->id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($currentUserCurrentGame) {
                            // Проверяем, что текущая игра не ультра мошна и не переданная игра
                            if ($currentUserCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                                return ErrorService::message('Вы не можете это сделать, так как текущую игру, вы получили от другого игрока');
                            }

                            if ($currentUserCurrentGame->type === PlayerGame::TYPE_PURSE) {
                                return ErrorService::message('Вы не можете это сделать, так как текущая игра - это ультра мошна');
                            }
                        } else if (!$currentUserCurrentGame) {
                            return ErrorService::message('Вы не можете это сделать, так как у вас нет текущей игры');
                        }

                        // Проверяем, что у игрока, которуму хотим передеать этой игры не было
                        $playerGameCheck = PlayerGame::query()->where('board_game_game_list_id', $currentUserCurrentGame->board_game_game_list_id)
                            ->findByBoardGame($this->conditionData['boardGame']->id)->findByUserId($player->user_id)->first();

                        if ($playerGameCheck) {
                            return ErrorService::message('У данного игрока уже была игра, которой вы хотите обменяться');
                        }

                        $firstPlayerGame = PlayerGame::query()
                            ->where('user_id', $player->user_id)
                            ->where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($firstPlayerGame) {
                            $status = PlayerGame::QUEUE;
                        } else {
                            $status = PlayerGame::CURRENT;
                        }

                        $newGameFields = [
                            'user_id' => $player->user_id,
                            'board_game_game_list_id' => $currentUserCurrentGame->board_game_game_list_id,
                            'status' => $status,
                            'board_game_id' => $this->conditionData['boardGame']->id,
                            'type' => PlayerGame::TYPE_PURSE,
                            'from_user_id' => $this->conditionData['user']->id,
                            'created_by' => $this->conditionData['user']->id,
                        ];

                        if (PlayerGame::create($newGameFields)) {
                            $currentUserCurrentGame->status = PlayerGame::GIVEN_AWAY;
                            $currentUserCurrentGame->save();

                            $message .= 'Использовал предмет ' . $this->item->item->name . ' на игрока ' . $player->user->name . ' и выбрал игру ' . $currentUserCurrentGame->game->game->name;

                            if ($message) {
                                $message .= ' ';
                            }

                            if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                                $notificationMessage = $message . ' Сообщение от игрока: ' . $data->additionalParams['message'];
                            }

                            $dontSendNotification = false;

                            if (isset($action->sendNotification) && $action->sendNotification === false) {
                                $dontSendNotification = true;
                            }

                            if (!$dontSendNotification) {
                                $this->actionService->createNotification($player, $notificationMessage);
                            }
                        }

                        return [
                            'logMessage' => $message,
                            'returnMessage' => $message,
                        ];
                    }
                }
                break;
            case 'holy-grenade':
                $useResult = null;

                $players = $this->actionService->target($data, $action);

                $currentPlayer = BoardGamePlayer::query()
                    ->findByUserId($this->conditionData['user']->id)
                    ->findByBoardGame($this->conditionData['boardGame']->id)
                    ->first();

                if (gettype($players) === 'array') {
                    foreach ($players as $player) {
                        if (abs($currentPlayer->position - $player->position) > 30) {
                            return ErrorService::message('Данный игрок находился дальше чем 30 клеток');
                        }

                        $newPosition = null;

                        if ($currentPlayer->position < $player->position) {
                            $values = explode('-', $action->value[0]);
                            $cellCountForPush = mt_rand($values[0], $values[1]);
                            $newPosition = $player->position + $cellCountForPush;
                            $useResult = [
                                'type' => 'forward',
                                'value' => $newPosition,
                            ];
                        } else if ($currentPlayer->position > $player->position) {
                            $values = explode('-', $action->value[1]);
                            $cellCountForPush = mt_rand($values[0], $values[1]);
                            $newPosition = $player->position - $cellCountForPush;
                            $useResult = [
                                'type' => 'back',
                                'value' => $newPosition,
                            ];
                        }

                        $newPosition = BoardService::checkPosition($newPosition, $this->conditionData['boardGame']);

                        $defaultMessage = 'позицию игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $player->position . ' - ' . $newPosition . ')';
                        $logMessage = 'Изменил ' . $defaultMessage;
                        $message = 'Вы изменили ' . $defaultMessage;

                        $this->useResult = $useResult;

                        if (isset($logMessage)) {
                            LogService::addLog(
                                $this->conditionData['user']->id,
                                $this->conditionData['boardGame']->id,
                                $logMessage,
                                $this->conditionData['player']->id,
                            );
                        }

                        $positionParams = [
                            'position' => $newPosition,
                            'player' => $player,
                        ];

                        BoardService::setPosition($positionParams, $this->conditionData,false, false);

                        $this->actionService->notificationHandler($data, $player, $action);

                        return [
                            'returnMessage' => $message,
                        ];
                    }
                }
                break;
            case 'reversible-pistol':
                $useResult = null;

                $players = $this->actionService->target($data, $action);

                foreach ($players as $player) {
                    if (mt_rand(1, 100) <= $action->value[2] ?? 10) {
                        $resultPoints = $player->points + $action->value[0];
                        $defaultMessage = 'количество очков игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $player->points . ' - ' . $resultPoints . ')';
                        $playerFields = ['points' => $resultPoints];
                        $player->update($playerFields);
                        $useResult = [
                            'type' => 'addPoints',
                            'value' => $action->value[0],
                        ];
                    } else {
                        $resultPoints = $player->points - $action->value[0];
                        $defaultMessage = 'количество очков игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $player->points . ' - ' . $resultPoints . ')';
                        $playerFields = ['points' => $resultPoints];
                        $player->update($playerFields);
                        $useResult = [
                            'type' => 'removePoints',
                            'value' => $action->value[1],
                        ];
                    }

                    $logMessage = 'Изменил ' . $defaultMessage;
                    $message = 'Вы изменили ' . $defaultMessage;

                    $this->useResult = $useResult;

                    if (isset($logMessage)) {
                        LogService::addLog(
                            $this->conditionData['user']->id,
                            $this->conditionData['boardGame']->id,
                            $logMessage,
                            $player->id,
                        );
                    }

                    $this->actionService->notificationHandler($data, $player, $action);

                    return [
                        'returnMessage' => $message,
                    ];
                }
                break;
        }
    }
}
