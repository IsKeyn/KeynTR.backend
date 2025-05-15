<?php

namespace App\Services\BoardGame;

use App\Models\Block;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameItem;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\User\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseItemService
{
    public function useItem(
        Request $request,
        BoardGamePlayer $BoardGamePlayer,
        StatusEffect $statusEffect,
        PlayerStatusEffect $PlayerStatusEffect,
        BoardGamePlayerPosition $BoardGamePlayerPosition,
        BoardGameItem $BoardGameItem,
        BoardGameInventory $BoardGameInventory,
        Notification $notification
    ) {
        $user = $request->user();

        /* Только авторизованный пользователь может применять предметы */
        if ($user) {
            /* Получаем информацию из инветаря пользователя о предмете, предмет должен участвовать в текущей настольной игре */
            $usedInventoryItem = $BoardGameInventory
                ->where('id', $request->id)
                ->where('user_id', $user->id)
                ->where('board_game_id', $request->board_game_id)
                ->where('has_used', false)
                ->first();

            if ($usedInventoryItem && $usedInventoryItem->board_game_item_id) {
                /* Получаем сущность самого предмета с его данными */
                $item = $BoardGameItem->where('id', $usedInventoryItem->board_game_item_id)->first();

                /* Предмет должен иметь JSON действий, если его нет, то предмет предназначен для "ручного" использования */
                if ($item->actions) {
                    foreach (json_decode($item->actions) as $action) {
                        if ($action->type) {
                            switch ($action->type) {
                                case 'removePoints':
                                case 'addPoints':
                                    $this->actionsWithPoints($request, $action, $user, $BoardGamePlayer,
                                        $BoardGamePlayerPosition, $notification);
                                    break;

                                case 'movePlayer':
                                case 'pushPlayer':
                                    $this->actionsWithPosition($request, $user, $action, $BoardGamePlayer,
                                        $BoardGamePlayerPosition, $notification);
                                    break;

                                case 'removeNegativeItem':
                                case 'stealItem':
                                case 'changeUserOwner':
                                case 'removeItem':
                                    $this->actionsWithItems($request, $user, $action, $BoardGamePlayer,
                                        $BoardGameInventory, $BoardGamePlayerPosition, $notification);
                                    break;

                                case 'applyStatusEffect':
                                    $this->activateEffect($request, $user, $action, $statusEffect, $BoardGamePlayer,
                                        $PlayerStatusEffect, $BoardGamePlayerPosition, $notification);
                                    break;
                            }
                        }
                    }
                }
            } else {
                return $this->error('Предмета нет в инвентаре или он был использован');
            }

            $usedItemsFields = ['has_used' => true];

            return $usedInventoryItem->update($usedItemsFields);
        } else {
            return $this->error('Только авторизованный пользователь, может применять предметы, авторизуйтесь');
        }
    }

    private function target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition)
    {
        $players = [];

        /* Функция, которое определяет, на кого действует предмет */
        switch ($action->target) {
            case 'current':
                $players[] = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->first();
                break;

            case 'other':
            case 'nearestPlayer':
            case 'fromTo':
            case str_contains($action->target, 'noFurther'):
                /* TODO ближащий игрок выбрается на фронте, проверять его тут на беке */
                if (isset($request->additionalParams['player']) && $request->additionalParams['player']) {
                    $players[] = $BoardGamePlayer->where('id', $request->additionalParams['player'])->where('board_game_id', $request->board_game_id)->first();
                } else {
                    return $this->error('Игрок не выбран');
                }
                break;

            case 'allExpectMe':
                $playersSelection = $BoardGamePlayer->where('board_game_id', $request->board_game_id)->where('user_id', '!=', $user->id)->get();

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
                    $players[] = $BoardGamePlayer->where('user_id', $playerByPosition->user_id)->where('board_game_id', $request->board_game_id)->first();
                }
                break;
        }

        return $players;
    }

    private function actionsWithPoints($request, $action, $user, $BoardGamePlayer, $BoardGamePlayerPosition, $notification)
    {
        /* Функция выполняет действия связанные с очками игрока */
       $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

        if (gettype($players) === 'array') {
            foreach ($players as $player) {
                $playerFields = $this->setFieldsWithPoints($request, $player, $action, $BoardGamePlayerPosition);
                $player->update($playerFields);

                $dontSendNotification = false;

                if (isset($action->sendNotification) && $action->sendNotification === false) {
                    $dontSendNotification = true;
                }

                if (!$dontSendNotification) {
                    $this->createNotification($player, $user, $request, $notification);
                }
            }
        }
    }

    private function setFieldsWithPoints($request, $player, $action, $BoardGamePlayerPosition)
    {
        if (is_int($action->value)) {
            if ($action->type === 'addPoints') {
                $playerFields = ['points' => $player->points + $action->value];
            } elseif ($action->type === 'removePoints') {
                $playerFields = ['points' => $player->points - $action->value];
            }
        } else {
            if ($action->value === 'playersAheadCount') {
                $playerPosition = $BoardGamePlayerPosition
                    ->where('user_id', $player->user_id)
                    ->where('board_game_id', $request->board_game_id)
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
                $explodedString = explode('_', $action->value);

                $currentPlayerPosition = $BoardGamePlayerPosition
                    ->where('user_id', $player->user_id)
                    ->where('board_game_id', $request->board_game_id)
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

        return $playerFields;
    }

    private function actionsWithPosition($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition, $notification)
    {
        $players = $this->target($request, $user, $action, $BoardGamePlayer, $BoardGamePlayerPosition);

        foreach ($players as $player) {
            $playerPosition = $BoardGamePlayerPosition
                ->where('user_id', $player->user_id)
                ->where('board_game_id', $request->board_game_id)
                ->orderBy('id', 'desc')->first();

            $playerPositionFields = [
                'user_id' => $player->user_id,
                'board_game_id' => $request->board_game_id,
                'created_by' => $user->id,
            ];

            /* Проверяем, что игрок не дальше позиции указанной в $action->target */
            if (str_contains($action->target, 'noFurther')) {
                /* Позиция игрока, который использует предмет */
                $usedItemPlayerPosition = $BoardGamePlayerPosition
                    ->where('user_id', $user->id)
                    ->where('board_game_id', $request->board_game_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($usedItemPlayerPosition->position > $playerPosition->position) {
                    $playerPositionFields['position'] = $playerPosition->position - $action->value;
                } else if ($usedItemPlayerPosition->position < $playerPosition->position) {
                    $playerPositionFields['position'] = $playerPosition->position + $action->value;
                }
            } else if (isset($action->direction)) {
                if ($action->direction === 'forward') {
                    $playerPositionFields['position'] = $playerPosition->position + $action->value;
                } elseif ($action->direction === 'back') {
                    $playerPositionFields['position'] = $playerPosition->position - $action->value;
                }
            }

            $BoardGamePlayerPosition->create($playerPositionFields);

            $dontSendNotification = false;

            if (isset($action->sendNotification) && $action->sendNotification === false) {
                $dontSendNotification = true;
            }

            if (!$dontSendNotification) {
                $this->createNotification($player, $user, $request, $notification);
            }
        }
    }

    private function actionsWithItems($request, $user, $action, $BoardGamePlayer, $BoardGameInventory, $BoardGamePlayerPosition, $notification)
    {
        /* Удаление всех предметов */
        if ($action->type === 'removeItem' && $action->target === 'all' && $action->itemId) {
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
                                        $this->createNotification($player, $user, $request, $notification);
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
                                    $this->createNotification($player, $user, $request, $notification);
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
                                            $this->createNotification($player, $user, $request, $notification);
                                            $this->createNotification($secondPlayer, $user, $request, $notification);
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
                            $this->createNotification($player, $user, $request, $notification);
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

    private function error($message)
    {
        /* Функция возврата ошибок действий с предметами */
        return response()->json(['error' => $message])->setStatusCode(Response::HTTP_OK);
    }

    public function createNotification($player, $user, $request, $notification) {
        if (
            isset($player) && $player
            && isset($request->additionalParams['message'])
            && $request->additionalParams['message']
            && $player->user_id !== $user->id
        ) {
            $fields = [
                'user_id' => $player->user_id,
                'created_by' => $user->id,
                'message' => $request->additionalParams['message'],
            ];

            $notification->create($fields);
        }
    }
}
