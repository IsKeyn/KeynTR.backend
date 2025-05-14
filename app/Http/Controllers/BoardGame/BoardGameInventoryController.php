<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameItem;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\Media;
use App\Models\User\Notification;
use App\Services\TagService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BoardGameInventoryController extends Controller
{
    public function index(BoardGameInventory $BoardGameInventory) {
        return $BoardGameInventory::all();
    }

    public function list(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventory = $BoardGameInventory->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->get();
            return BoardGameInventoryResource::collection($inventory);
        } else {
            return false;
        }
    }

    public function add(Request $request) {
        $fields = $request->validate([
            'board_game_id' => 'required',
            'board_game_item_id' => 'required',

        ]);

        $fields['user_id'] = $request->user()->id;
        $fields['created_by'] = $request->user()->id;

        $user = $request->user();

        if ($user) {
            if ($BoardGameInventory = BoardGameInventory::create($fields)) {
                return $BoardGameInventory;
            }
        }

        return false;
    }

    public function destroy(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventoryItem = $BoardGameInventory->where('id', $request->id)->where('user_id', $user->id)->where('board_game_id', $request->board_game_id);
            return $inventoryItem->delete();
        } else {
            return false;
        }
    }

    public function useItem(
        Request $request,
        BoardGameInventory $BoardGameInventory,
        BoardGameItem $BoardGameItem,
        BoardGamePlayer $BoardGamePlayer,
        Notification $notification,
        StatusEffect $statusEffect,
        PlayerStatusEffect $PlayerStatusEffect,
        BoardGamePlayerPosition $BoardGamePlayerPosition
    ) {
        /*
         * Переименовать переменную player в players
         * Разнести на разные функции и в сервис
         */

        $user = $request->user();

        if ($user) {
            $usedInventoryItem = $BoardGameInventory
                ->where('id', $request->id)
                ->where('user_id', $user->id)
                ->where('board_game_id', $request->board_game_id)
                ->where('has_used', false)
                ->first();

            if ($usedInventoryItem && $usedInventoryItem->board_game_item_id) {
                $item = $BoardGameItem->where('id', $usedInventoryItem->board_game_item_id)->first();

                if ($item->actions) {
                    foreach (json_decode($item->actions) as $action) {
                        switch ($action->type) {
                            case 'removePoints':
                            case 'addPoints':
                                $dontUpdate = false;

                                if ($action->target === 'current') {
                                    $player = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->first();
                                }

                                if (
                                    ($action->target === 'other' || $action->target === 'nearestPlayer')
                                    && isset($request->additionalParams['player'])
                                    && $request->additionalParams['player']
                                ) {
                                    $player = $BoardGamePlayer->where('id', $request->additionalParams['player'])->first();
                                } else if ($action->target === 'allExpectMe') {
                                    $players = $BoardGamePlayer->where('board_game_id', $request->board_game_id)->where('user_id', '!=', $user->id)->get();

                                    foreach ($players as $player) {
                                        $playerFields = ['points' => $player->points + $action->value];
                                        $player->update($playerFields);
                                        $dontUpdate = true;
                                    }
                                } else if ($action->target === 'positionLeader') {
                                    $players = $BoardGamePlayerPosition::all()->sortByDesc('created_at')->unique('user_id');

                                    $playersByPositions = [];

                                    foreach ($players as $valuePlayer) {
                                        $playersByPositions[$valuePlayer->position][] = $valuePlayer;
                                    }

                                    foreach ($playersByPositions[max(array_keys($playersByPositions))] as $playerByPosition) {
                                        $player[] = $BoardGamePlayer->where('user_id', $playerByPosition->user_id)->where('board_game_id', $request->board_game_id)->first();
                                    }
                                }

                                if (!$dontUpdate) {
                                    if (gettype($player) === 'array') {
                                        foreach ($player as $value) {
                                            $playerFields = self::setPlayerPoints($request, $value, $action, $BoardGamePlayerPosition);
                                            $value->update($playerFields);
                                        }
                                    } else {
                                        $playerFields = self::setPlayerPoints($request, $player, $action, $BoardGamePlayerPosition);
                                        $player->update($playerFields);
                                    }
                                }
                                break;

                            case 'movePlayer':
                            case 'pushPlayer':
                                if ($action->target === 'current') {
                                    $player = $BoardGamePlayer->where('user_id', $user->id)->first();
                                }

                                if (
                                    ($action->target === 'other'
                                        || $action->target === 'nearestPlayer'
                                        || str_contains($action->target, 'noFurther')
                                    )
                                    && isset($request->additionalParams['player'])
                                    && $request->additionalParams['player']
                                ) {
                                    $player = $BoardGamePlayer->where('id', $request->additionalParams['player'])->first();
                                }

                                $playerPosition = $BoardGamePlayerPosition->where('user_id', $player->user_id)->where('board_game_id', $request->board_game_id)->orderBy('id', 'desc')->first();

                                $playerPositionFields = [
                                    'user_id' => $player->user_id,
                                    'board_game_id' => $request->board_game_id,
                                    'created_by' => $user->id,
                                ];

                                /* TODO проверять, что игрок не дальше стольки-то клеток */
                                if (str_contains($action->target, 'noFurther')) {
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
                                }

                                if (isset($action->direction)) {
                                    if ($action->direction === 'forward') {
                                        $playerPositionFields['position'] = $playerPosition->position + $action->value;
                                    } elseif ($action->direction === 'back') {
                                        $playerPositionFields['position'] = $playerPosition->position - $action->value;
                                    }
                                }

                                $BoardGamePlayerPosition->create($playerPositionFields);
                                break;

                            case 'removeNegativeItem':
                            case 'stealItem':
                            case 'changeUserOwner':
                                if ($request->additionalParams) {
                                    if (isset($request->additionalParams['player']) && $request->additionalParams['player']) {
                                        $player = $BoardGamePlayer->where('id', $request->additionalParams['player'])->first();

                                        if (isset($request->additionalParams['item']) && $request->additionalParams['item']) {
                                            $inventoryItem = $BoardGameInventory
                                                ->where('user_id', $player->user_id)
                                                ->where('id', $request->additionalParams['item'])
//                                                ->where('has_used', false)
                                                ->first();


                                            if ($action->type === 'removeNegativeItem') {
                                                if ($inventoryItem && $inventoryItem->item->type === 1) {
                                                    return $inventoryItem->delete();
                                                } else {
                                                    // Выбросить ошибку, предмет не негативный
                                                }
                                            }

                                            if ($action->type === 'stealItem') {
                                                if ($inventoryItem) {
                                                    $playerFields = [
                                                        'user_id' => $user->id,
                                                    ];

                                                    $inventoryItem->update($playerFields);
                                                } else {
                                                    // Выбросить ошибку, предмет не негативный
                                                }
                                            }

                                            if (
                                                $action->type === 'changeUserOwner'
                                                && isset($request->additionalParams['secondPlayer'])
                                                && $request->additionalParams['secondPlayer']
                                            ) {
                                                $secondPlayer = $BoardGamePlayer->where('id', $request->additionalParams['secondPlayer'])->first();

                                                if ($inventoryItem) {
                                                    $playerFields = [
                                                        'user_id' => $secondPlayer->user_id,
                                                    ];

                                                    $inventoryItem->update($playerFields);
                                                } else {
                                                    // Выбросить ошибку, предмет не негативный
                                                }
                                            }
                                        }
                                    }
                                }

//                                if ($action->target === 'other') {
//                                    $player = $BoardGamePlayer->where('user_id', $user->id)->first();
//                                }

                                //                                    if ($action->type === 'addPoints') {
//                                        $playerFields = ['points' => $player->points + $action->value];
//                                    } elseif ($action->type === 'removePoints') {
//                                        $playerFields = ['points' => $player->points - $action->value];
//                                    }
//
//                                    $player->update($playerFields);
                                break;
                            case 'removeItem':
                                if ($action->target === 'all') {
                                    if ($action->itemId) {
                                        $items = $BoardGameInventory->where('board_game_item_id', $action->itemId);

                                        $arUserIds = [];

                                        foreach ($items->get() as $item1) {
                                            if ($item1->user_id !== $user->id && !in_array($item1->user_id, $arUserIds))  {
                                                $fields = [
                                                    'user_id' => $item1->user_id,
                                                    'created_by' => $user->id,
                                                    'message' => $user->name . ' использовал предмет "' . $item->name . '"',
                                                ];

                                                $notification->create($fields);

                                                $arUserIds[] = $item1->user_id;
                                            }

                                            $items->delete();
                                        }
                                    }
                                }
                                break;
                            case 'applyStatusEffect':
                                if ($action->target === 'other' && $action->value) {
                                    $statusEffectObj = $statusEffect->where('slug', $action->value)->first();

                                    if (isset($request->additionalParams['player']) && $request->additionalParams['player']) {
                                        $player = $BoardGamePlayer->where('id', $request->additionalParams['player'])->first();

                                        if ($player) {
                                            $PlayerStatusEffectFields = [
                                                'user_id' => $player->user_id,
                                                'board_game_id' => $statusEffectObj->board_game_id,
                                                'status_effect_id' => $statusEffectObj->id,
                                                'created_by' => $user->id,
                                            ];

                                            $PlayerStatusEffect->create($PlayerStatusEffectFields);
                                        }

                                    }
                                }
                                break;
                        }

                        if (gettype($player) === 'array') {
                            foreach ($player as $value) {
                                self::createNotification($value, $user, $request, $notification);
                            }
                        } else {
                            self::createNotification($player, $user, $request, $notification);
                        }
                    }
                }
            } else {
                return response()->json(['error' => 'Предмета нет в инвентаре или он был использован'])->setStatusCode(Response::HTTP_OK);
            }

            $usedItemsFields = ['has_used' => true];

            return $usedInventoryItem->update($usedItemsFields);
        } else {
            return false;
        }
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

    public function setPlayerPoints($request, $player, $action, $BoardGamePlayerPosition) {
        if (is_int($action->value)) {
            if ($action->type === 'addPoints') {
                $playerFields = ['points' => $player->points + $action->value];
            } elseif ($action->type === 'removePoints') {
                $playerFields = ['points' => $player->points - $action->value];
            }
        } else {
            if ($action->value === 'playersAheadCount') {
                $playerPosition = $BoardGamePlayerPosition->where('user_id', $player->user_id)->where('board_game_id', $request->board_game_id)->orderBy('id', 'desc')->first();

                $playersAhead = $BoardGamePlayerPosition::where('position', '>', $playerPosition->position)->where('user_id', '!=', $player->user_id)->get()->sortByDesc('created_at')->unique('user_id');
                $playersAheadCount = count($playersAhead);

                if ($action->type === 'addPoints') {
                    $playerFields = ['points' => $player->points + $playersAheadCount];
                } elseif ($action->type === 'removePoints') {
                    $playerFields = ['points' => $player->points - $playersAheadCount];
                }
            } else if (str_contains($action->value, 'forEachNear')) {
                $explodedString = explode('_', $action->value);

                $currentPlayerPosition = $BoardGamePlayerPosition->where('user_id', $player->user_id)->where('board_game_id', $request->board_game_id)->orderBy('id', 'desc')->first();
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

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'description' => 'sometimes',
            'active' => 'sometimes',
            'image' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($BoardGameInventory = BoardGameInventory::create($fields)) {

            if (isset($fields['image'])) {
                $media = Media::query()->where('id', $fields['image'])->first();
                $BoardGameInventory->media()->syncWithPivotValues($media->id, ['type' => 1]);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($BoardGameInventory, $fields['tags']);
            }

            return $BoardGameInventory;
        }
    }

    public function update(Request $request, BoardGameInventory $BoardGameInventory) {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'description' => 'sometimes',
            'active' => 'sometimes',
            'image' => 'sometimes',
        ]);

        if (isset($fields['image'])) {
            $media = Media::query()->where('id', $fields['image'])->first();
            $BoardGameInventory->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($BoardGameInventory, $fields['tags']);
        }

        return $BoardGameInventory->update($fields);
    }

    public function edit(BoardGameInventory $BoardGameInventory)
    {
        return BoardGameInventoryResource::make($BoardGameInventory);
    }
}
