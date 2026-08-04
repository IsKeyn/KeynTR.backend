<?php

namespace App\Services\BoardGame;

use App\Filters\BoardGame\BgPlayerFilter;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\Item;
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
    private $userId = null;

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

    public function activateAction(
        $data,
        $action,
        $userId = null // $userId передается для случаев, когда надо подменить текущего пользователя, например, когда перемещаешь игрока на ячейку игрового поля с эффектом
    )
    {
        $this->userId = $userId;

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

            case 'removeNegativeEffect':
            case 'stealEffect':
            case 'changeUserOwnerEffect':
                $result = $this->actionsWithEffects(
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

            case 'itemRoll':
                $result = $this->actionsWithPlayerNumberField(
                    $data,
                    $action,
                    'item_roll_count',
                    'количетво круток рулетки с предметами'
                );
                break;

            case 'stepCount':
                $result = $this->actionsWithPlayerNumberField(
                    $data,
                    $action,
                    'step_count',
                    'доступное количество шагов по игровому полю'
                );
                break;

            case 'streak':
                $result = $this->actionsWithPlayerNumberField(
                    $data,
                    $action,
                    'streak',
                    'стрик'
                );
                break;

            case 'rerolledOwnGame':
                $result = $this->actionsWithPlayerNumberField(
                    $data,
                    $action,
                    'rerolled_own_game_count',
                    'количество своих рерольнутых игр'
                );
                break;

            case 'selectGame':
            case 'rerollGame':
                $result = $this->actionsWithGame(
                    $data,
                    $action,
                );
                break;

            case 'random':
                $result = $this->randomAction(
                    $data,
                    $action,
                );
                break;

            case 'game':
                $result = $this->gameAction(
                    $data,
                    $action,
                );
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

                if ($action->count) {
                    $playersAheadCount = $playersAheadCount * $action->count;
                }

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
                $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' предметом ' . $this->itemElement->item->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';
            } else if ($this->type === 'statusEffect') {
                $logMessage = 'Изменил количество очков игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->points . ' - ' . $playerFields["points"] . ')';
            }
        }

        if (isset($logMessage)) {
            LogService::addLog(
                $this->conditionData['user']->id,
                $this->conditionData['boardGame']->id,
                $logMessage,
                $player->id
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
                $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' предметом ' . $this->itemElement->item->name . ' (' . $playerPosition->position . ' - ' . $playerPositionFields['position'] . ')';
            } else if ($this->type === 'statusEffect') {
                $logMessage = 'Изменил позицию игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->points . ' - ' . $playerPositionFields["points"] . ')';
            }

            if (isset($logMessage)) {
                LogService::addLog(
                    $this->conditionData['user']->id,
                    $this->conditionData['boardGame']->id,
                    $logMessage,
                    $player->id
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
                    if (!($action->value ?? null)) {
                        return ErrorService::message(__('actions.action_value_null'));
                    }

                    $item = Item::findBySlug($action->value)->active()->first();

                    if (!$item) {
                        return ErrorService::message(__('actions.item_not_found'));
                    }

                    $ItemBind = ItemBind::findByBoardGame($this->conditionData['boardGame']->id)->where('item_id', $item->id)->active()->first();

                    if ($ItemBind) {
                        $inventoryFields = [
                            'user_id' => $this->conditionData['user']->id,
                            'board_game_id' => $this->conditionData['boardGame']->id,
                            'board_game_item_id' => $ItemBind->id,
                        ];

                        $result = null;

                        if ($action->type === 'addItem') {
                            $result = $this->BoardGameInventory::create($inventoryFields);

                            if ($result) {
                                $this->notificationHandler($data, $player, $action);
                            }
                        }

                        return $result;
                    } else {
                        return ErrorService::message('Предмет не найден');
                    }
                }
            }
        }
    }

    private function actionsWithEffects($data, $action)
    {
        /* Удаление статус эффекта у всех игроков */
        if ($action->type === 'removeStatusEffect' && $action->target === 'all' && $action->slug) {
            $statusEffect = StatusEffect::findBySlug($action->slug)->first();

            if (!$statusEffect) {
                ErrorService::message('Не найдено статус эффекта');
            }

            $usersStatusEffects = PlayerStatusEffect::where('status_effect_id', $statusEffect->id)->active()->get();

            $arUserIds = [];

            foreach ($usersStatusEffects as $userStatusEffect) {
                if ($userStatusEffect->user_id !== $this->conditionData['user']->id && !in_array($userStatusEffect->user_id, $arUserIds))  {
                    $fields = [
                        'user_id' => $userStatusEffect->user_id,
                        'created_by' => $this->conditionData['user']->id,
                        'message' => $this->conditionData['user']->name . ' использовал предмет "' . $userStatusEffect->statusEffect->name . '"',
                    ];

                    $this->notification::create($fields);

                    $arUserIds[] = $userStatusEffect->user_id;
                }

                $usersStatusEffects->delete();
            }
        } else {
            $players = $this->target($data, $action);

            foreach ($players as $player) {
                if ($data->additionalParams['selectedEffect'] ?? null) {
                    $statusEffect = PlayerStatusEffect::findByUserId($player->user_id)
                        ->where('id', $data->additionalParams['selectedEffect'])
                        ->active()
                        ->first();

                    if (isset($statusEffect)) {
                        switch ($action->type) {
                            case 'removeNegativeEffect':
                                if ($statusEffect->statusEffect->debuff === true) {
                                    $statusEffect->delete();

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
                                    return $this->error('Вы пытаетесь удалить статус эффект, который не является предметом с дебафом');
                                }
                                break;
                            case 'stealEffect':
                                $playerFields = [
                                    'user_id' => $this->conditionData['user']->id,
                                ];

                                $statusEffect->update($playerFields);

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
                            case 'changeUserOwnerEffect':
                                if (
                                    isset($data->additionalParams['secondPlayer'])
                                    && $data->additionalParams['secondPlayer']
                                ) {
                                    $secondPlayer = $this->BoardGamePlayer::query()->where('id', $data->additionalParams['secondPlayer'])->first();

                                    if ($secondPlayer) {
                                        $playerFields = [
                                            'user_id' => $secondPlayer->user_id,
                                        ];

                                        $statusEffect->update($playerFields);

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
                        return $this->error('Статус эффекта не найдено');
                    }
                } else if ($action->value ?? null) {
                    $statusEffect = StatusEffect::findBySlug($action->value)->active()->first();

                    if ($statusEffect) {
                        $inventoryFields = [
                            'user_id' => $this->conditionData['user']->id,
                            'board_game_id' => $this->conditionData['boardGame']->id,
                            'status_effect_id' => $statusEffect->id,
                            'active' => true,
                            'created_by' => $this->conditionData['user']->id,
                        ];

                        $result = null;

                        if ($action->type === 'addStatusEffect') {
                            $result = PlayerStatusEffect::create($inventoryFields);

                            if ($result) {
                                $this->notificationHandler($data, $player, $action);
                            }
                        }

                        return $result;
                    } else {
                        return ErrorService::message('Статус эффект не найден');
                    }
                }
            }
        }
    }

    private function actionsWithTime($data, $action)
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
        if (!$action->value) {
            return ErrorService::message('Отсутствует тип взаимодействия $action->value');
        }

        $players = $this->target($request, $action);

        $this->conditionData['player']->load([
            'currentGames',
        ]);

        foreach ($players as $player) {
            $player->load([
                'currentGames',
            ]);

            switch ($action->value) {
                case 'switchGame':

                    // 1. Проверяем игру текущего игрока и может ли он её передать
                    $currentUserCurrentGame = $this->conditionData['player']->currentGames->first();

                    if (!$currentUserCurrentGame) {
                        return ErrorService::message('Вы не можете это сделать, так как у вас нет текущей игры');
                    }

                    // Проверяем, что текущая игра не ультра мошна и не переданная игра
                    if ($currentUserCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                        return ErrorService::message('Вы не можете это сделать, так как текущую игру, вы получили от другого игрока');
                    }

                    if ($currentUserCurrentGame->type === PlayerGame::TYPE_PURSE) {
                        return ErrorService::message('Вы не можете это сделать, так как текуая игра - это ультра мошна');
                    }

                    // 2. Проверяем, что у игрока обмена есть текущая игра
                    $playerCurrentGame = $player->currentGames->first();

                    if (!$playerCurrentGame) {
                        return ErrorService::message('У данного игрока отсуствует текущая игра');
                    }

                    // Проверяем, что текущая игра не ультра мошна и не переданная игра
                    if ($playerCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                        return ErrorService::message('Текущая игра участника, которого вы выбрали является переданной игрой');
                    }

                    if ($playerCurrentGame->type === PlayerGame::TYPE_PURSE) {
                        return ErrorService::message('Текущая игра участника, которого вы выбрали является ультра мошной');
                    }

                    // 3. Проверяем, что у игрока этой игры не было
                    $playerGameCheck = PlayerGame::query()
                        ->where('board_game_game_list_id', $currentUserCurrentGame->board_game_game_list_id)
                        ->findByBoardGame($this->conditionData['boardGame']->id)
                        ->findByUserId($player->user_id)
                        ->first();

                    if ($playerGameCheck) {
                        return ErrorService::message('У данного игрока уже была игра, которой вы хотите обменяться');
                    }

                    // 4. Проверяем, что у вас не было игры, на которую вы хотите обмениваетесь
                    $userGameCheck = PlayerGame::query()
                        ->where('board_game_game_list_id', $playerCurrentGame->board_game_game_list_id)
                        ->findByBoardGame($this->conditionData['boardGame']->id)
                        ->findByUserId($this->conditionData['user']->id)
                        ->first();

                    if ($userGameCheck) {
                        return ErrorService::message('У вас уже была игра, на которую вы хотите обменяться');
                    }

                    return $this->createInteraction($request, $action, $player);
                case 'playForMe':
                    $currentUserCurrentGame = $this->conditionData['player']->currentGames->first();

                    if (!$currentUserCurrentGame) {
                        return ErrorService::message('Вы не можете это сделать, так как у вас нет текущей игры');
                    }

                    // Проверяем, что текущая игра не ультра мошна и не переданная игра
                    if ($currentUserCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                        return ErrorService::message('Вы не можете это сделать, так текущую игру, вы получили от другого игрока');
                    }

                    if ($currentUserCurrentGame->type === PlayerGame::TYPE_PURSE) {
                        return ErrorService::message('Вы не можете это сделать, так как текущая игра - это ультра мошна');
                    }

                    // 3. Проверяем, что у игрока этой игры не было
                    $playerGame = PlayerGame::query()
                        ->where('board_game_game_list_id', $currentUserCurrentGame->board_game_game_list_id)
                        ->findByBoardGame($this->conditionData['boardGame']->id)
                        ->findByUserId($player->user_id)
                        ->first();

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
    }

    private function createInteraction($request, $action, $player)
    {
        $fields = [
            'type' => $action->value,
            'status' => PlayerInteractions::STATUS_ACTIVE,
            'board_game_id' => $this->conditionData['boardGame']->id,
            'bg_player_id' => $this->conditionData['player']->id,
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

                $notificationMessage .= ' Примите решение на странице взаимодействий';

                $actions = [
                    [
                        'type' => 'button',
                        'button' => [
                            'name' => 'Открыть взаимодействия',
                            'href' => '/e/' .  $this->conditionData['boardGame']->slug . '/player-interactions/',
                        ],
                    ]
                ];

                $this->createNotification($player, $notificationMessage, false, $actions);
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
            $conditionData = $this->conditionData;

            $boardGameId = $conditionData['boardGame']->id ?? null;

            if (!$boardGameId) {
                throw new \InvalidArgumentException('Board game is not defined in condition data.');
            }

            $bindFilter = fn ($query) => $query->where('board_game_id', $boardGameId);

            $statusEffectObj = StatusEffect::query()
                ->where('slug', $action->value)
                ->whereHas('statusEffectBinds', $bindFilter)
                ->with(['statusEffectBinds' => $bindFilter])
                ->active()
                ->first();

            if (!$statusEffectObj) {
                return $this->error('Статус эффект отсутствует');
            }

            if (!$statusEffectObj->statusEffectBinds || $statusEffectObj->statusEffectBinds->isEmpty()) {
                return $this->error('Статус эффект не привязан к данному ивенту');
            }

            $players = $this->target($request, $action);

            if (gettype($players) === 'array') {
                foreach ($players as $player) {
                    $PlayerStatusEffectFields = [
                        'user_id' => $player->user_id,
                        'bg_player_id' => $player->id,
                        'board_game_id' => $this->conditionData['boardGame']->id,
                        'status_effect_bind_id' => $statusEffectObj->statusEffectBinds->first()->id,
                        'created_by' => $this->conditionData['user']->id,
                    ];

                    $this->PlayerStatusEffect::create($PlayerStatusEffectFields);

                    $logMessage = 'Получил статус эффект ' . $statusEffectObj->name;

                    if (isset($logMessage)) {
                        LogService::addLog(
                            $this->conditionData['user']->id,
                            $this->conditionData['boardGame']->id,
                            $logMessage,
                            $player->id
                        );
                    }

                    $this->notificationHandler($request, $player, $action);
                }

                return true;
            }
        } else {
            return $this->error('Действие отсутствует');
        }
    }

    private function actionsWithPlayerNumberField($data, $action, $columnName, $fieldHumanName)
    {
        /* Функция выполняет действия связанные с полем игрока из таблицы board_game_players, которое не может быть меньше 0 */
        $players = $this->target($data, $action);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) === 'array') {
            foreach ($players as $player) {
                $value = $this->getValueAndSetLog($action, $player, $columnName, $fieldHumanName);
                $player->update([$columnName => $value]);
                $this->notificationHandler($data, $player, $action);
            }

            return true;
        }
    }

    private function actionsWithGame($data, $action)
    {
        $players = $this->target($data, $action);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) === 'array') {
            foreach ($players as $player) {
                if ($action->type === 'selectGame') {
                    if ($data->additionalParams['selectedGame'] ?? null) {
                        // Проверяем, что у игрока нет текущей игры
                        $currentUserCurrentGame = PlayerGame::where('board_game_id', $this->conditionData['boardGame']->id)
                            ->where('user_id', $this->conditionData['user']->id)
                            ->where('status', PlayerGame::CURRENT)->first();

                        if ($currentUserCurrentGame) {
                            return ErrorService::message('Вы не можете сейчас использовать этот предмет, так как у Вас есть текущая игра');
                        }

                        $newGameFields = [
                            'user_id' => $player->user_id,
                            'board_game_game_list_id' => $data->additionalParams['selectedGame'],
                            'status' => PlayerGame::CURRENT,
                            'board_game_id' => $this->conditionData['boardGame']->id,
                            'from_user_id' => $this->conditionData['user']->id,
                            'created_by' => $this->conditionData['user']->id,
                        ];

                        if ($playerGame = PlayerGame::create($newGameFields)) {
                            $this->notificationHandler($data, $player, $action);

                            $logMessage = 'использовал предмет "' . $this->itemElement->item->name . '" и выбрал игру ' . $playerGame->game->game->name;

                            return $logMessage;
                        }
                    } else {
                     return ErrorService::message('Не выбрана игра');
                    }
                } elseif ($action->type === 'rerollGame') {
                    if ($data->additionalParams['selectedGame'] ?? null) {
                        $newGameFields = [
                            'user_id' => $player->user_id,
                            'board_game_game_list_id' => $data->additionalParams['selectedGame'],
                            'status' => PlayerGame::REROLLED,
                            'board_game_id' => $this->conditionData['boardGame']->id,
                            'type' => PlayerGame::TYPE_PURSE,
                            'from_user_id' => $this->conditionData['user']->id,
                            'created_by' => $this->conditionData['user']->id,
                        ];

                        if ($playerGame = PlayerGame::create($newGameFields)) {
                            $this->notificationHandler($data, $player, $action);

                            $logMessage = 'использовал предмет "' . $this->itemElement->item->name . '" и выбрал игру ' . $playerGame->game->game->name;

                            return $logMessage;
                        }
                    } else {
                        return ErrorService::message('Не выбрана игра');
                    }
                }
            }

            return true;
        }
    }

    private function randomAction($data, $action)
    {
        $players = $this->target($data, $action);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) !== 'array') {
            return false;
        }

        $resultByPlayers = [];

        foreach ($players as $player) {
            if ($action->actionsForRandom) {
                $randomKey = array_rand($action->actionsForRandom);
                $randomAction = $action->actionsForRandom[$randomKey];

                if ($this->activateAction($data, (object) $randomAction, $player->user_id)) {
                    $resultByPlayers[$player->id] = $randomAction;
                }
            }
        }

        return $resultByPlayers;
    }

    private function gameAction($data, $action)
    {
        $players = $this->target($data, $action);

        if (isset($players['error'])) {
            return $players['error'];
        }

        if (gettype($players) !== 'array') {
            return false;
        }

        $resultByPlayers = [];

        foreach ($players as $player) {
            if ($action->actionsForRandom) {
                $randomKey = array_rand($action->actionsForRandom);
                $randomAction = $action->actionsForRandom[$randomKey];

                if ($this->activateAction($data, (object) $randomAction, $player->user_id)) {
                    $resultByPlayers[$player->id] = $randomAction;
                }
            }
        }

        return $resultByPlayers;
    }

    private function getValueAndSetLog($action, $player, $columnName, $fieldHumanName)
    {
        if (!isset($action->changeType) || !$action->changeType) {
            return ErrorService::message('Не получен тип изменения');
        }

        if (!isset($action->value) || !$action->value) {
            return ErrorService::message('Не получено значение для изменения');
        }

        switch ($action->changeType) {
            case 'add':
                $value = $player->$columnName + $this->getValue($action->value);
                break;

            case 'remove':
                $value = $player->$columnName + $this->getValue($action->value);
                break;

            case 'set':
                $value = $this->getValue($action->value);
                break;
        }

        if ($value < 0) {
            $value = 0;
        }

        if (isset($action->logMessage) && $action->logMessage) {
            $logMessage = $this->prepareMessage($action, 'logMessage');
        } else {
            if ($this->type === 'item') {
                $logMessage = 'Изменил ' . $fieldHumanName . ' игрока ' . $player->user->name . ' предметом ' . $this->itemElement->item->name . ' (' . $player->$columnName . ' - ' . $value . ')';
            } else if ($this->type === 'statusEffect') {
                $logMessage = 'Изменил ' . $fieldHumanName . ' игрока ' . $player->user->name . ' статус эффектом ' . $this->statusEffectElement->name . ' (' . $player->$columnName . ' - ' . $value . ')';
            } else {
                $logMessage = 'Изменил ' . $fieldHumanName . ' игрока ' . $player->user->name . ' (' . $player->points . ' - ' . $value . ')';
            }
        }

        if (isset($logMessage)) {
            LogService::addLog(
                $this->conditionData['user']->id,
                $this->conditionData['boardGame']->id,
                $logMessage,
                $player->id
            );
        }

        return $value;
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

    public function target($request, $action): array
    {
        $players = [];

        /* Функция определяющая игроков, которые являются целью */
        switch ($action->target) {
            case 'current':
                $players[] = $this->conditionData['player'];
                break;
            case 'all':
                $players = $this->BoardGamePlayer::query()
                    ->findByBoardGame($this->conditionData['boardGame']->id)
                    ->get()
                    ->all();
                break;
            case 'oneOfAll':
            case 'other':
            case 'fromTo':
                if (!isset($request->additionalParams['player']) || !$request->additionalParams['player']) {
                    return $this->error('Игрок не выбран');
                }

                $query = $this->BoardGamePlayer::query()
                    ->findByBoardGame($this->conditionData['boardGame']->id)
                    ->active();

                if ($request->additionalParams['player'] === 'randomPlayer') {
                    $query->inRandomOrder();
                } else {
                    $query->where('id', $request->additionalParams['player']);
                }

                $players[] = $query->first();
                break;

            case 'notPlayBattleForPoints':
                $filters = [
                    'notPlayBattleForPoints' => [
                        'user_id' => $this->conditionData['user']->id,
                        'bg_slug' => $this->conditionData['boardGame']->slug,
                    ]
                ];

                $filterRequest = new \Illuminate\Http\Request(['filters' => json_encode($filters)]);
                $filter = new BgPlayerFilter($filterRequest);

                $query = $filter
                    ->apply(BoardGamePlayer::where('board_game_id', $this->conditionData['boardGame']->id));

                if ($request->additionalParams['player'] === 'randomPlayer') {
                    $query->inRandomOrder();
                } else {
                    $query->where('id', $request->additionalParams['player']);
                }

                $players[] = $query->first();
                break;

            case 'notInvitedToCoop':
                $filters = [
                    'notInvitedToCoop' => [
                        'user_id' => $this->conditionData['user']->id,
                        'bg_slug' => $this->conditionData['boardGame']->slug,
                    ]
                ];

                $filterRequest = new \Illuminate\Http\Request(['filters' => json_encode($filters)]);
                $filter = new BgPlayerFilter($filterRequest);

                $query = $filter
                    ->apply(BoardGamePlayer::where('board_game_id', $this->conditionData['boardGame']->id));

                if (!isset($request->additionalParams['player']) || $request->additionalParams['player'] === 'randomPlayer') {
                    $query->inRandomOrder();
                } else {
                    $query->where('id', $request->additionalParams['player']);
                }

                $players[] = $query->first();
                break;

            case 'nearestPlayer':
                $filters = [
                    'nearestOnly' => [
                        'user_id' => $this->conditionData['user']->id,
                        'bg_slug' => $this->conditionData['boardGame']->slug,
                    ]
                ];

                $filterRequest = new \Illuminate\Http\Request(['filters' => json_encode($filters)]);
                $filter = new BgPlayerFilter($filterRequest);

                $query = $filter
                    ->apply(BoardGamePlayer::where('board_game_id', $this->conditionData['boardGame']->id));

                if ($request->additionalParams['player'] === 'randomPlayer') {
                    $query->inRandomOrder();
                } else {
                    $query->where('id', $request->additionalParams['player']);
                }

                $players[] = $query->first();
                break;

            case str_contains($action->target, 'noFurther'):
                $parts = explode('_', $action->target);
                $result = $parts[1];

                $filters = [
                    'distance' => [
                        'user_id' => $this->conditionData['user']->id,
                        'max_distance' => $result,
                    ]
                ];

                $filterRequest = new \Illuminate\Http\Request(['filters' => json_encode($filters)]);
                $filter = new BgPlayerFilter($filterRequest);

                $query = $filter->apply(BoardGamePlayer::where('board_game_id', $this->conditionData['boardGame']->id));

                if ($request->additionalParams['player'] === 'randomPlayer') {
                    $query->inRandomOrder();
                } else {
                    $query->where('id', $request->additionalParams['player']);
                }

                $players[] = $query->first();
                break;

            case 'allExpectMe':
                $players = $this->BoardGamePlayer::query()
                    ->findByBoardGame($this->conditionData['boardGame']->id)
                    ->where('user_id', '!=', $this->conditionData['user']->id)
                    ->get()->all();
                break;

            case 'positionLeader':
                $playersWithPositions = $this->BoardGamePlayerPosition::all()
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->sortByDesc('created_at')
                    ->unique('user_id');

                $modelWithMaxPosition = $playersWithPositions->sortByDesc('position')->first();

                $players[] = $this->BoardGamePlayer::query()
                    ->where('user_id', $modelWithMaxPosition->user_id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->first();
                break;
        }

        return $players;
    }

    public function notificationHandler($data, $player, $action)
    {
        $dontSendNotification = false;

        if (isset($action->sendNotification) && $action->sendNotification === false) {
            $dontSendNotification = true;
        }

        if (!$dontSendNotification) {
            if ($data->additionalParams['message'] ?? null) {
                $notificationMessage = $data->additionalParams['message'];
                $this->createNotification($player, $notificationMessage);
            } elseif ($action->message ?? null) {
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

    public function createNotification($player, $message, $ignoreSelf = false, $actions = null) {
        if (
            isset($player) && $player
            && $message
            && ($ignoreSelf || $player->user_id !== $this->conditionData['user']->id)
        ) {
            $fields = [
                'user_id' => $player->user_id,
                'created_by' => $this->conditionData['user']->id,
                'message' => $message,
                'actions' => $actions,
                'entity_type' => $this->conditionData['boardGame']::class,
                'entity_id' => $this->conditionData['boardGame']->id,
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
