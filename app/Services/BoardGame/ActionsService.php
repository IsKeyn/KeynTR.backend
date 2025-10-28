<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\BoardGame\Timer;
use App\Models\User\Notification;
use App\Services\ErrorService;

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

    public function activateAction($data, $action)
    {
        switch ($action->type) {
            case 'removePoints':
            case 'addPoints':
                $result = $this->actionsWithPoints(
                    $data,
                    $action,
                );
                break;

            case 'movePlayer':
            case 'pushPlayer':
                $result = $this->actionsWithPosition(
                    $data,
                    $action,
                );
                break;

            case 'removeNegativeItem':
            case 'stealItem':
            case 'changeUserOwner':
            case 'removeItem':
            case 'addItem':
                $result = $this->actionsWithItems(
                    $data,
                    $action,
                );
                break;

            case 'applyStatusEffect':
                $result = $this->activateEffect(
                    $data,
                    $action,
                );
                break;


            case 'addTime':
                $result = $this->actionsWithTime(
                    $data,
                    $action,
                );
                break;

            case 'playerInteractions':
                $result = $this->actionsWithPlayerInteractions(
                    $data,
                    $action,
                );
                break;
            case 'customItem':

                break;
        }

        return $result;
    }

    private function actionsWithPoints($data, $action)
    {
        /* Функция выполняет действия связанные с очками игрока */
        $players = $this->target($data, $action);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) === 'array') {
            foreach ($players as $player) {
                $playerFields = $this->setFieldsWithPoints($player, $action);
                $player->update($playerFields);

                $this->notificationHandler($data, $player, $action);
            }

            return true;
        }
    }

    public function setFieldsWithPoints($player, $action, $logMessage = null)
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

        if (isset($action->logMessage) && $action->logMessage) {
            $logMessage = $this->prepareMessage($action, 'logMessage');
        } else {
            if ($this->type === 'item') {
                $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';
            } else if ($this->type === 'statusEffect') {
                $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';
            }
        }

        if (isset($logMessage)) {
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

            $playerPositionFields = [];

            $value = $this->getValue($action->value);

            if (str_contains($action->target, 'noFurther')) {  // Проверяем, что игрок не дальше позиции указанной в $action->target
                /* Позиция игрока, который использует предмет */
                $usedItemPlayerPosition = $this->BoardGamePlayerPosition::query()
                    ->where('user_id', $this->conditionData['user']->id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($usedItemPlayerPosition->position > $playerPosition->position) {
                    $playerPositionFields['position'] = BoardService::checkPosition($playerPosition->position - $value, $this->conditionData['boardGame']);
                } else if ($usedItemPlayerPosition->position < $playerPosition->position) {
                    $playerPositionFields['position'] = BoardService::checkPosition($playerPosition->position + $value, $this->conditionData['boardGame']);
                }
            } else if (isset($action->direction)) {
                if ($action->direction === 'forward') {
                    $playerPositionFields['position'] = BoardService::checkPosition($playerPosition->position + $value, $this->conditionData['boardGame']);
                } elseif ($action->direction === 'back') {
                    $playerPositionFields['position'] = BoardService::checkPosition($playerPosition->position - $value, $this->conditionData['boardGame']);
                }
            }

            if ($this->type === 'item') {
                $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' предметом ' . $this->item->item->name . ' (' . $playerPosition->position . ' - ' . $playerPositionFields['position'] . ')';
            } else if ($this->type === 'statusEffect') {
                $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->points . ' - ' . $playerPositionFields["points"] . ')';
            }

            if (isset($logMessage)) {
                LogService::addLog(
                    $this->conditionData['user']->id,
                    $this->conditionData['boardGame']->id,
                    $logMessage
                );
            }

            $positionParams = [
                'position' => $playerPositionFields['position'],
                'player' => $player,
            ];

            BoardService::setPosition($positionParams, $this->conditionData,false, false);

            $this->notificationHandler($request, $player, $action);
        }

        return true;
    }

    private function actionsWithItems($data, $action)
    {
        /* Удаление предмета у всех игроков */
        if ($action->type === 'removeItem' && $action->target === 'all' && $action->itemId) {
            // TODO переделать, должен брать не привязанный предмет ид а оригинальный
            $items = $this->BoardGameInventory::query()->where('board_game_item_id', $action->itemId);

            $arUserIds = [];

            foreach ($items->get() as $item) {
                if ($item->user_id !== $this->conditionData['user']->id && !in_array($item->user_id, $arUserIds))  {
                    $fields = [
                        'user_id' => $item->user_id,
                        'created_by' => $this->conditionData['user']->id,
                        'message' => $this->conditionData['user']->name . ' использовал предмет "' . $item->item->name . '"',
                    ];

                    $this->notification::create($fields);

                    $arUserIds[] = $item->user_id;
                }

                $items->delete();
            }
        } else {
            $players = $this->target($data, $action);

            foreach ($players as $player) {
                if (isset($data->additionalParams['item']) && $data->additionalParams['item']) {
                    if ($data->additionalParams['item']) {
                        $inventoryItem = $this->BoardGameInventory::query()
                            ->where('user_id', $player->user_id)
                            ->where('id', $data->additionalParams['item'])
                            ->where('has_used', false)
                            ->first();
                    }

                    if (isset($inventoryItem)) {
                        switch ($action->type) {
                            case 'removeNegativeItem':
                                if ($inventoryItem->item->type === 1) {
                                    $inventoryItem->delete();

                                    $dontSendNotification = false;

                                    if (isset($action->sendNotification) && $action->sendNotification === false) {
                                        $dontSendNotification = true;
                                    }

                                    if (!$dontSendNotification) {
                                        if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                                            $notificationMessage = $data->additionalParams['message'];
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
                                    if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                                        $notificationMessage = $data->additionalParams['message'];
                                        $this->createNotification($player, $notificationMessage);
                                    }
                                }
                                break;
                            case 'changeUserOwner':
                                if (
                                    isset($data->additionalParams['secondPlayer'])
                                    && $data->additionalParams['secondPlayer']
                                ) {
                                    $secondPlayer = $this->BoardGamePlayer::query()->where('id', $data->additionalParams['secondPlayer'])->first();

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
                                            if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                                                $notificationMessage = $data->additionalParams['message'];
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
                } else if ($action->value ?? null) {
                    $ItemBind = ItemBind::findByBoardGame($this->conditionData['boardGame']->id)->where('slug', $action->value)->active()->first();

                    if ($ItemBind) {
                        $inventoryFields = [
                            'user_id' => $this->conditionData['user']->id,
                            'board_game_id' => $this->conditionData['boardGame']->id,
                            'board_game_item_id' => $ItemBind->id,
                        ];

                        $result = $this->BoardGameInventory::create($inventoryFields);

                        if ($result) {
                            $this->notificationHandler($data, $player, $action);
                        }

                        return $result;
                    } else {
                        return ErrorService::message('Предмет не найден');
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
                        // 1. Проверяем игру текущего игрока и может ли он её передать
                        $currentUserCurrentGame = PlayerGame::where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('user_id', $this->conditionData['user']->id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($currentUserCurrentGame) {
                            // Проверяем, что текущая игра не ультра мошна и не переданная игра
                            if ($currentUserCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                                return ErrorService::message('Вы не можете это сделать, так как текущую игру, вы получили от другого игрока');
                            }

                            if ($currentUserCurrentGame->type === PlayerGame::TYPE_PURSE) {
                                return ErrorService::message('Вы не можете это сделать, так как текуая игра - это ультра мошна');
                            }
                        } else if (!$currentUserCurrentGame) {
                            return ErrorService::message('Вы не можете это сделать, так как у вас нет текущей игры');
                        }

                        // 2. Проверяем, что у игрока обмена есть текущая игра
                        $playerCurrentGame = PlayerGame::where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('user_id', $player->user_id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($playerCurrentGame) {
                            // Проверяем, что текущая игра не ультра мошна и не переданная игра
                            if ($playerCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                                return ErrorService::message('Текущая игра участника, которого вы выбрали является переданной игрой');
                            }

                            if ($playerCurrentGame->type === PlayerGame::TYPE_PURSE) {
                                return ErrorService::message('Текущая игра участника, которого вы выбрали является ультра мошной');
                            }
                        } else if (!$playerCurrentGame) {
                            return ErrorService::message('У данного игрока отсуствует текущая игра');
                        }

                        // 3. Проверяем, что у игрока этой игры не было
                        $playerGameCheck = PlayerGame::query()->where('board_game_game_list_id', $currentUserCurrentGame->board_game_game_list_id)
                            ->findByBoardGame($this->conditionData['boardGame']->id)->findByUserId($player->user_id)->first();

                        if ($playerGameCheck) {
                            return ErrorService::message('У данного игрока уже была игра, которой вы хотите обменяться');
                        }

                        // 4. Проверяем, что у  вас не было игры, на которую вы хотите обмениваетесь
                        $userGameCheck = PlayerGame::query()->where('board_game_game_list_id', $playerCurrentGame->board_game_game_list_id)
                            ->findByBoardGame($this->conditionData['boardGame']->id)->findByUserId($this->conditionData['user']->id)->first();

                        if ($userGameCheck) {
                            return ErrorService::message('У вас уже была игра, на которую вы хотите обменяться');
                        }

                        return $this->createInteraction($request, $action, $player);
                    case 'playForMe':
                        $currentUserCurrentGame = PlayerGame::where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('user_id', $this->conditionData['user']->id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($currentUserCurrentGame) {
                            // Проверяем, что текущая игра не ультра мошна и не переданная игра
                            if ($currentUserCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                                return ErrorService::message('Вы не можете это сделать, так текущую игру, вы получили от другого игрока');
                            }

                            if ($currentUserCurrentGame->type === PlayerGame::TYPE_PURSE) {
                                return ErrorService::message('Вы не можете это сделать, так как текущая игра - это ультра мошна');
                            }
                        } else if (!$currentUserCurrentGame) {
                            return ErrorService::message('Вы не можете это сделать, так как у вас нет текущей игры');
                        }

                        // 3. Проверяем, что у игрока этой игры не было
                        $playerGame = PlayerGame::query()->where('board_game_game_list_id', $currentUserCurrentGame->board_game_game_list_id)
                            ->findByBoardGame($this->conditionData['boardGame']->id)->findByUserId($player->user_id)->first();

                        if ($playerGame) {
                            return ErrorService::message('У данного игрока уже была игра, которую вы предлагаете');
                        }

                        if ($action->description) {
                            $action->description = str_replace('*name*', $currentUserCurrentGame->game->game->name, $action->description);
                        }

                        return $this->createInteraction($request, $action, $player);

                    case 'battleForPoints':
                    case 'inviteToCoop':
                        return $this->createInteraction($request, $action, $player);
                }
            }
        } else {
            return ErrorService::message('Отсутствует тип взаимодействия $action->value');
        }
    }

    private function createInteraction($request, $action, $player)
    {
        $fields = [
            'type' => $action->value,
            'status' => PlayerInteractions::STATUS_ACTIVE,
            'board_game_id' => $this->conditionData['boardGame']->id,
            'created_by' => $this->conditionData['user']->id,
            'with_player' => $player->user_id,
            'active' => true,
            'description' => isset($action->description) ? $action->description : null,
        ];

        if ($request && $request->id && $this->type === 'item') {
            $fields['entity_id'] = $request->id;
            $fields['entity_type'] = BoardGameInventory::class;
        }

        if ($request && $request->id && $this->type === 'positionEffect') {
            $fields['entity_id'] = $request->id;
            $fields['entity_type'] = BoardPositionEffectsBind::class;
        }

        if (PlayerInteractions::create($fields)) {
            $dontSendNotification = false;

            if (isset($action->sendNotification) && $action->sendNotification === false) {
                $dontSendNotification = true;
            }

            if (!$dontSendNotification) {
                if ($request->additionalParams['message'] ?? null) {
                    $notificationMessage = $request->additionalParams['message'];
                }

                $this->createNotification($player, $notificationMessage);
            }

            return 'Ваш запрос успешно отправлен игроку';
        } else {
            return ErrorService::message('Ошибка создания взаимодействия игроков');
        }
    }

    private function activateEffect($request, $action)
    {
        /* Функция выполняет действия связанные с эффектами игрока */
        if ($action->value) {
            $statusEffectObj = $this->statusEffect::query()->where('slug', $action->value)->first();

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

                        $this->PlayerStatusEffect::create($PlayerStatusEffectFields);

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

    private function notificationHandler($data, $player, $action)
    {
        $dontSendNotification = false;

        if (isset($action->sendNotification) && $action->sendNotification === false) {
            $dontSendNotification = true;
        }

        if (!$dontSendNotification) {
            if (isset($data->additionalParams['message']) && $data->additionalParams['message']) {
                $notificationMessage = $data->additionalParams['message'];
                $this->createNotification($player, $notificationMessage);
            } elseif (isset($action->message) && $action->message) {
                $message = $this->prepareMessage($action, 'message');
                $this->createNotification($player, $message, true);
            }
        }
    }

    private function prepareMessage($action, $keyMessage)
    {
        if (property_exists($action, $keyMessage)) {
            $message = $action->$keyMessage;

            if (property_exists($action, 'name') && $action->name) {
                $message = str_replace('*name', $action->name, $message);
            }

            if (property_exists($action, 'value') && $action->value) {
                $message = str_replace('*value', $action->value, $message);
            }

            return $message;
        }
    }

    public function createNotification($player, $message, $ignoreSelf = false) {
        if (
            isset($player) && $player
            && $message
            && ($ignoreSelf || $player->user_id !== $this->conditionData['user']->id)
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
