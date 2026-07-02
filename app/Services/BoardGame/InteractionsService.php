<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Services\Entity\EntityService;
use App\Services\ErrorService;
use App\Services\NotificationService;

class InteractionsService
{
    private $interaction;
    private $conditionData = [];

    public function action($request)
    {
        if (!$request->slug) return ErrorService::message('Не получен SLUG');
        if (!$request->id) return ErrorService::message('Не получен ID взаимодействия');
        if (!$request->type) return ErrorService::message('Не получен тип взаимодействия');

        return $this->init($request->slug, $request->id, $request->type);
    }

    public function init($slug, $id, $type)
    {
        if (!$slug) return ErrorService::message('Не получен SLUG');
        if (!$id) return ErrorService::message('Не получен ID взаимодействия');
        if (!$type) return ErrorService::message('Не получен тип взаимодействия');

        $this->conditionData = PlayerGameService::checkConditions($slug);

        if (isset($this->conditionData['status']) && $this->conditionData['status'] === 'error') {
            return $this->conditionData;
        } else {
            $this->interaction = $this->checkInteraction($id);

            if (isset($this->interaction['status']) && $this->interaction['status'] === 'error') {
                return $this->interaction;
            } else {
                switch ($type) {
                    case 'accept': return $this->accept();
                    case 'refuse': return $this->refuse();
                    case 'systemRefuse': return $this->systemRefuse();
                    case 'recall': return $this->recall();
                    case 'iwin': return $this->iwin();
                    case 'ilose': return $this->ilose();
                }
            }
        }
    }

    private function accept()
    {
        if ($this->interaction->with_player !== $this->conditionData['user']->id) {
            return ErrorService::message('Вы не можете принять взаимодействие, которое отправлено не вам');
        }

        if ($this->interaction->status === PlayerInteractions::STATUS_ACTIVE) {
            if ($this->interaction->created_by) {
                $message = 'Принял предложение "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '"';

                $fields = [
                    'user_id' => $this->interaction->created_by,
                    'created_by' => $this->conditionData['user']->id,
                    'message' => $message,
                    'actions' => $this->getActions(),
                    'entity_type' => $this->conditionData['boardGame']::class,
                    'entity_id' => $this->conditionData['boardGame']->id,
                ];

                NotificationService::set($fields);

                // Устанавливаем логи
                if ($this->conditionData['boardGame']->id) {
                    LogService::addLog(
                        $this->conditionData['user']->id,
                        $this->conditionData['boardGame']->id,
                        $message,
                        $this->conditionData['player']->id,
                    );
                }
            }

            $active = false;

            if ($this->interaction->type === 'switchGame') {
                $firstPlayerGame = PlayerGame::query()
                    ->where('user_id', $this->interaction->with_player)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('status', PlayerGame::CURRENT)->first();

                $secondPlayerGame = PlayerGame::query()
                    ->where('user_id', $this->interaction->created_by)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('status', PlayerGame::CURRENT)->first();

                // Делаем текущие игры переданными
                $firstPlayerGame->status = PlayerGame::GIVEN_AWAY;
                $secondPlayerGame->status = PlayerGame::GIVEN_AWAY;

                $firstPlayerGame->update();
                $secondPlayerGame->update();

                // Создаем новые игры
                $newGameFieldsForFirstPlayer = [
                    'user_id' => $this->interaction->with_player,
                    'board_game_game_list_id' => $secondPlayerGame->board_game_game_list_id,
                    'status' => PlayerGame::CURRENT,
                    'board_game_id' => $this->conditionData['boardGame']->id,
                    'created_by' => $this->interaction->created_by,
                ];

                PlayerGame::create($newGameFieldsForFirstPlayer);

                $newGameFieldsForSecondPlayer = [
                    'user_id' => $this->interaction->created_by,
                    'board_game_game_list_id' => $firstPlayerGame->board_game_game_list_id,
                    'status' => PlayerGame::CURRENT,
                    'board_game_id' => $this->conditionData['boardGame']->id,
                    'created_by' => $this->conditionData['user']->id,
                ];

                PlayerGame::create($newGameFieldsForSecondPlayer);
                $this->checkInteractionAfterActionWithGame($this->interaction->type, $this->conditionData);
            }

            if ($this->interaction->type === 'battleForPoints' || $this->interaction->type === 'inviteToCoop') {
                $active = true;
            }

            if ($this->interaction->type === 'playForMe') {
                $firstPlayerGame = PlayerGame::query()
                    ->where('user_id', $this->interaction->with_player)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('status', PlayerGame::CURRENT)->first();

                $secondPlayerGame = PlayerGame::query()
                    ->where('user_id', $this->interaction->created_by)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('status', PlayerGame::CURRENT)->first();

                if ($secondPlayerGame) {
                    if ($firstPlayerGame) {
                        $status = PlayerGame::QUEUE;
                    } else {
                        $status = PlayerGame::CURRENT;
                    }

                    $newGameFields = [
                        'user_id' => $this->interaction->with_player,
                        'board_game_game_list_id' => $secondPlayerGame->board_game_game_list_id,
                        'status' => $status,
                        'board_game_id' => $this->conditionData['boardGame']->id,
                        'type' => PlayerGame::TYPE_TAKEN,
                        'created_by' => $this->conditionData['user']->id,
                        'from_user_id' => $this->interaction->created_by,
                    ];

                    PlayerGame::create($newGameFields);

                    $secondPlayerGame->status = PlayerGame::GIVEN_AWAY;
                    $secondPlayerGame->save();
                }
            }
        } else {
            return ErrorService::message('Вы не можете принять взаимодействие, которое находится в статусе отличном от "Отправлено"');
        }

        $this->interaction->status = PlayerInteractions::STATUS_ACCEPTED;

        $this->interaction->active = $active;
        return $this->interaction->save();
    }

    private function refuse()
    {
        if ($this->interaction->with_player !== $this->conditionData['user']->id) {
            return ErrorService::message('Вы не можете отказаться от взаимодействия, которое отправлено не вам');
        }
        if ($this->interaction->status === PlayerInteractions::STATUS_ACTIVE) {
            if ($this->interaction->created_by) {
                $message =  $this->interaction->withPlayerData->name . 'Отказался от предложения "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '"';

                $fields = [
                    'user_id' => $this->interaction->created_by,
                    'created_by' => $this->conditionData['user']->id,
                    'message' => $message,
                    'actions' => $this->getActions(),
                    'entity_type' => $this->conditionData['boardGame']::class,
                    'entity_id' => $this->conditionData['boardGame']->id,
                ];

                NotificationService::set($fields);

                // Устанавливаем логи
                if ($this->conditionData['boardGame']->id) {
                    LogService::addLog(
                        $this->conditionData['user']->id,
                        $this->conditionData['boardGame']->id,
                        $message,
                        $this->conditionData['player']->id,
                    );
                }
            }

            if ($this->interaction->entity_id && $this->interaction->entity_type) {
                if ($this->interaction->type === 'switchGame' || $this->interaction->type === 'playForMe') {
                    if ($this->interaction->entity_type === 'App\Models\BoardGame\BoardGameInventory') {
                        $inventoryItem = $this->interaction->entity_type::findById($this->interaction->entity_id)->first();

                        if ($inventoryItem->has_used) {
                            $inventoryItem->has_used = false;
                            $inventoryItem->save();
                        }
                    }
                }
            }
        } else {
           return ErrorService::message('Вы не можете отозвать взаимодействие, которое находится в статусе отличном от "Отправлено"');
        }

        $this->interaction->status = PlayerInteractions::STATUS_REFUSED;
        $this->interaction->active = false;
        return $this->interaction->save();
    }

    private function systemRefuse()
    {
        if ($this->interaction->created_by) {
            $message = 'предложение отправленное ' . $this->interaction->withPlayerData->name . ' было отозвано системой "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '"';

            $fields = [
                'user_id' => $this->interaction->created_by,
                'created_by' => $this->conditionData['user']->id,
                'message' => $message,
                'actions' => $this->getActions(),
                'entity_type' => $this->conditionData['boardGame']::class,
                'entity_id' => $this->conditionData['boardGame']->id,
            ];

            NotificationService::set($fields);

            // Устанавливаем логи
            if ($this->conditionData['boardGame']->id) {
                LogService::addLog(
                    $this->conditionData['user']->id,
                    $this->conditionData['boardGame']->id,
                    $message,
                    $this->conditionData['player']->id,
                );
            }
        }

        if ($this->interaction->entity_id && $this->interaction->entity_type) {
            if ($this->interaction->type === 'switchGame') {
                if ($this->interaction->entity_type === 'App\Models\BoardGame\BoardGameInventory') {
                    $inventoryItem = $this->interaction->entity_type::findById($this->interaction->entity_id)->first();

                    if ($inventoryItem->has_used) {
                        $inventoryItem->has_used = false;
                        $inventoryItem->save();
                    }
                }
            }
        }

        $this->interaction->status = PlayerInteractions::STATUS_REFUSED;
        $this->interaction->active = false;

        return $this->interaction->save();
    }

    private function recall($forced = false)
    {
        if ($this->interaction->created_by !== $this->conditionData['user']->id) {
            return ErrorService::message('Вы не можете отозвать взаимодействие, которое создано не вами');
        }

        if ($this->interaction->status === PlayerInteractions::STATUS_ACTIVE || $forced) {
            if ($this->interaction->with_player) {
                $message = 'Отозвал предложение "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '" отправленное ' . $this->interaction->withPlayerData->name;

                $fields = [
                    'user_id' => $this->interaction->with_player,
                    'created_by' => $this->conditionData['user']->id,
                    'message' => $message,
                    'actions' => $this->getActions(),
                    'entity_type' => $this->conditionData['boardGame']::class,
                    'entity_id' => $this->conditionData['boardGame']->id,
                ];

                NotificationService::set($fields);

                // Устанавливаем логи
                if ($this->conditionData['boardGame']->id) {
                    LogService::addLog(
                        $this->conditionData['user']->id,
                        $this->conditionData['boardGame']->id,
                        $message,
                        $this->conditionData['player']->id,
                    );
                }
            }

            if ($this->interaction->entity_id && $this->interaction->entity_type) {
                if ($this->interaction->type === 'switchGame') {
                    if ($this->interaction->entity_type === 'App\Models\BoardGame\BoardGameInventory') {
                        $inventoryItem = $this->interaction->entity_type::findById($this->interaction->entity_id)->first();

                        if ($inventoryItem->has_used) {
                            $inventoryItem->has_used = false;
                            $inventoryItem->save();
                        }
                    }
                }
            }

            $this->interaction->status = PlayerInteractions::RECALLED;
            $this->interaction->active = false;
            return $this->interaction->save();
        } else {
            return ErrorService::message('Вы не можете отозвать взаимодействие, которое находится в статусе отличном от "Отправлено"');
        }
    }

    private function iwin()
    {
        if ($this->interaction->created_by !== $this->conditionData['user']->id) {
            return ErrorService::message('Вы не можете принять решение в взаимодействии, которое создано не вами');
        }

        $active = false;

        if ($this->interaction->status === PlayerInteractions::STATUS_ACCEPTED) {
            if ($this->interaction->created_by) {
                $message = 'Принял решение о своей победе во взаимодействии "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '" c ' . $this->interaction->withPlayerData->name;;

                if ($this->interaction->description) {
                    $message .= ' ' . $this->interaction->description;
                }

                $fields = [
                    'user_id' => $this->interaction->with_player,
                    'created_by' => $this->conditionData['user']->id,
                    'message' => $message,
                    'actions' => $this->getActions(),
                    'entity_type' => $this->conditionData['boardGame']::class,
                    'entity_id' => $this->conditionData['boardGame']->id,
                ];

                NotificationService::set($fields);

                // Устанавливаем логи
                if ($this->conditionData['boardGame']->id) {
                    LogService::addLog(
                        $this->interaction->created_by,
                        $this->conditionData['boardGame']->id,
                        $message,
                        $this->conditionData['player']->id,
                    );
                }

                // Действия
                if ($this->interaction->entity_id && $this->interaction->entity_type) {
                    if ($this->interaction->type === 'battleForPoints') {
                        if ($this->interaction->entity_type === BoardPositionEffectsBind::class) {
                            $boardPositionEffectBind = $this->interaction->entity_type::findById($this->interaction->entity_id)->first();

                            if ($boardPositionEffectBind && $boardPositionEffectBind->boardPositionEffect) {
                                /* Эффект должен иметь JSON действий */
                                if ($boardPositionEffectBind->boardPositionEffect->actions) {
                                    $actionService = new ActionsService($this->conditionData, 'positionEffect', $boardPositionEffectBind->boardPositionEffect);

                                    BoardService::setUsePositionEffect(
                                        $this->conditionData['user']->id,
                                        $this->conditionData['boardGame']->id,
                                        $boardPositionEffectBind->position
                                    );

                                    foreach (json_decode($boardPositionEffectBind->boardPositionEffect->actions) as $action) {
                                        $player = BoardGamePlayer::query()
                                            ->findByBoardGame($this->conditionData['boardGame']->id)
                                            ->findByUserId($this->conditionData['user']->id)
                                            ->active()
                                            ->first();

                                        if ($player) {
                                            $actionForFunc = (object)['value' => $action->pointsForWin, 'type' => 'addPoints'];

                                            $logMessage = 'получил ' . $action->pointsForWin . ' очков, за победу в ' . $action->name;

                                            $playerFields = $actionService->setFieldsWithPoints($player, $actionForFunc, $logMessage);
                                            $player->update($playerFields);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return ErrorService::message('Вы не можете принять взаимодействие, которое находится в статусе отличном от "Отправлено"');
        }

        $this->interaction->status = PlayerInteractions::I_WIN;
        $this->interaction->active = $active;
        return $this->interaction->save();
    }

    private function ilose()
    {
        if ($this->interaction->created_by !== $this->conditionData['user']->id) {
            return ErrorService::message('Вы не можете принять решение в взаимодействии, которое создано не вами');
        }

        $active = false;

        if ($this->interaction->status === PlayerInteractions::STATUS_ACCEPTED) {
            if ($this->interaction->created_by) {
                $message = 'Принял решение о победе оппонента во взаимодействии "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '"';

                if ($this->interaction->description) {
                    $message .= ' ' . $this->interaction->description;
                }

                $fields = [
                    'user_id' => $this->interaction->with_player,
                    'created_by' => $this->conditionData['user']->id,
                    'message' => $message,
                    'actions' => $this->getActions(),
                    'entity_type' => $this->conditionData['boardGame']::class,
                    'entity_id' => $this->conditionData['boardGame']->id,
                ];

                NotificationService::set($fields);

                // Устанавливаем логи
                if ($this->conditionData['boardGame']->id) {
                    LogService::addLog($this->interaction->created_by, $this->conditionData['boardGame']->id, $message);
                }

                // Действия
                if ($this->interaction->entity_id && $this->interaction->entity_type) {
                    if ($this->interaction->type === 'battleForPoints') {
                        if ($this->interaction->entity_type === BoardPositionEffectsBind::class) {
                            $boardPositionEffectBind = $this->interaction->entity_type::findById($this->interaction->entity_id)->first();

                            if ($boardPositionEffectBind && $boardPositionEffectBind->boardPositionEffect) {
                                /* Эффект должен иметь JSON действий */
                                if ($boardPositionEffectBind->boardPositionEffect->actions) {
                                    $actionService = new ActionsService($this->conditionData, 'positionEffect', $boardPositionEffectBind->boardPositionEffect);

                                    BoardService::setUsePositionEffect(
                                        $this->conditionData['user']->id,
                                        $this->conditionData['boardGame']->id,
                                        $boardPositionEffectBind->position
                                    );

                                    foreach (json_decode($boardPositionEffectBind->boardPositionEffect->actions) as $action) {

                                        $player = BoardGamePlayer::query()
                                            ->findByBoardGame($this->conditionData['boardGame']->id)
                                            ->findByUserId($this->interaction->with_player)
                                            ->active()
                                            ->first();

                                        if ($player) {
                                            $actionForFunc = (object)['value' => $action->pointsForWin, 'type' => 'addPoints'];

                                            $logMessage = 'получил ' . $action->pointsForWin . ' очков, за победу в ' . $action->name;

                                            $playerFields = $actionService->setFieldsWithPoints($player, $actionForFunc, $logMessage);
                                            $player->update($playerFields);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return ErrorService::message('Вы не можете принять взаимодействие, которое находится в статусе отличном от "Отправлено"');
        }

        $this->interaction->status = PlayerInteractions::I_WIN;

        $this->interaction->active = $active;
        return $this->interaction->save();
    }

    private function getActions()
    {
        return [
            [
                'type' => 'button',
                'button' => [
                    'name' => 'Открыть взаимодействия',
                    'href' => '/e/' .  $this->conditionData['boardGame']->slug . '/player-interactions/',
                ],
            ]
        ];
    }

    private function checkInteraction($id)
    {
        $playerInteractions = PlayerInteractions::findById($id)->first();

        if (!$playerInteractions) {
            return [
                'status' => 'error',
                'status_message' => 'Взаимодействия не существует',
            ];
        }

        if (!$playerInteractions->active) {
            return [
                'status' => 'error',
                'status_message' => 'Взаимодействие не активно',
            ];
        }

        return $playerInteractions;
    }

    public function checkInteractionAfterActionWithGame($type, $conditionData)
    {
        $this->conditionData = $conditionData;

        if (
            $type === PlayerGame::REROLLED
            || $type === PlayerGame::COMPLETED
            || $type === 'switchGame'
        ) {
            $playerInteractions = PlayerInteractions::where('board_game_id', $conditionData['boardGame']->id)
                ->where(function($query) use ($conditionData) {
                    $query
                        ->where('created_by', '=', $conditionData['user']->id)
                        ->orWhere('with_player', '=', $conditionData['user']->id);
                })
                ->active()
                ->orderByDesc('id')
                ->get();

            foreach ($playerInteractions as $interaction) {
                if ($interaction->type === 'switchGame') {
                    $this->interaction = $interaction;

                    if ($interaction->with_player === $conditionData['user']->id) {
                        $this->systemRefuse();
                    }

                    if ($interaction->created_by === $conditionData['user']->id) {
                        $this->recall(true);
                    }
                }

                if ($interaction->type === 'inviteToCoop') {
                    if ($interaction->created_by === $conditionData['user']->id) {
                        $this->interaction = $interaction;

                        $this->recall(true);
                    }
                }
            }
        }
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            PlayerInteractions::class,
            PlayerInteractions::CACHE_SERVICE,
            PlayerInteractions::DETAIL_RESOURCE,
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
}
