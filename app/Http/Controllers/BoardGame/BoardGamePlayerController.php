<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\BoardGamePlayerFullResource;
use App\Http\Resources\BoardGame\BoardGamePlayerPositionsResource;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\BoardGamePlayerShortResource;
use App\Http\Resources\BoardGame\BoardGamePlayerWithCurrentGameResource;
use App\Http\Resources\BoardGame\BoardGamePlayerWithInventoryResource;
use App\Http\Resources\BoardGame\BoardGamePlayerWithStatusEffectsResource;
use App\Http\Resources\BoardGame\BoardGameShortResource;
use App\Http\Resources\BoardGame\ItemBindResource;
use App\Http\Resources\BoardGame\LogResource;
use App\Http\Resources\BoardGame\PlayerGameResource;
use App\Http\Resources\BoardGame\PlayerInteractionResource;
use App\Http\Resources\BoardGame\PlayerStatusEffectResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\User;
use App\Services\BoardGame\ItemService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\UseItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BoardGamePlayerController extends Controller
{
    public function getPlayer (
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        $user = User::findByName($name)->first();

        if ($user) {
//            $cacheKey = 'board_game_' . $slug . '_player_' . $user->id . '_cache';
//            $minutes = 60 * 24 * 30; // 30 дней
//
//            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGamePlayer, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $player = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $id)->first();

                if ($player) {
                    return BoardGamePlayerShortResource::make($player);
                } else {
                    return false;
                }
//            });
        } else {
            return false;
        }
    }

    public function getCurrent(
        $slug,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        if ($slug) {
            $user = Auth::user();

            if ($user) {
//            $cacheKey = 'board_game_' . $slug . '_player_' . $user->id . '_cache';
//            $minutes = 60 * 24 * 30; // 30 дней
//
//            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGamePlayer, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                if ($id) {
                    $player = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $id)->first();

                    if ($player) {
                        return BoardGamePlayerWithCurrentGameResource::make($player);
                    }
                }
//            });
            }
        }

        return false;
    }

    public function getList(
        $slug,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
//        $cacheKey = 'board_game_' . $slug . '_player_list_cache';
//        $minutes = 60 * 24 * 30; // 30 дней
//
//        return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGamePlayer, $slug) {
            $boardGameId = $BoardGame->findBySlug($slug)->value('id');
            $players = $BoardGamePlayer->where('board_game_id', $boardGameId)->get();

            return BoardGamePlayerWithStatusEffectsResource::collection($players);
//        });
    }

    public function getListWithInventory(
        Request $request,
        $slug,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
            $boardGameId = $BoardGame->findBySlug($slug)->value('id');

            // TODO сделать жадную загрузку для внутренних связей
            $players = $BoardGamePlayer->with(['user', 'positions', 'inventory', 'statusEffects', 'mainTimers'])->findByBoardGame($boardGameId)->active();

            $user = Auth::user();

            if ($user && ($request->type === 'battleForPoints' || $request->type === 'inviteToCoop'))
            {
                $boardGameInteractions = PlayerInteractions::query()
                    ->findByBoardGame($boardGameId)
                    ->where('created_by', $user->id)
                    ->where('type', $request->type)
                    ->whereIn('status', [PlayerInteractions::COOP_FINISH, PlayerInteractions::I_WIN, PlayerInteractions::I_LOSE])
                    ->select('with_player')->get();

                $playerInteractionsIds = [];

                foreach ($boardGameInteractions as $interaction) {
                    if (!array_search($interaction->with_player, $playerInteractionsIds)) {
                        $playerInteractionsIds[] = $interaction->with_player;
                    }
                }

                $players->whereNotIn('user_id', $playerInteractionsIds);
            }

            $players = $players->get();

            if ($request->type === 'battleForPoints' || $request->type === 'inviteToCoop') {
                return BoardGamePlayerShortResource::collection($players);
            } else {
                return BoardGamePlayerWithInventoryResource::collection($players);
            }
    }

    public function getEvents(
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        $userId = User::findByName($name)->value('id');

        if ($userId) {
            $id = $BoardGame->findBySlug($slug)->value('id');
            $playerInGames = $BoardGamePlayer->findByUserId($userId)->where('board_game_id', '!=', $id)->get();

            $BoardGames = collect([]);

            foreach ($playerInGames as $player) {
                $BoardGames->push($player->boardGame);
            }

            return BoardGameShortResource::collection($BoardGames);
        }
    }

    public function getInventory(
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGameInventory $BoardGameInventory
    )
    {
        $user = User::findByName($name)->first();

        if ($user) {
//            $cacheKey = 'board_game_' . $slug . '_player_inventory_' . $user->id . '_cache';
//            $minutes = 60 * 24 * 30; // 30 дней
//
//            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGameInventory, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $inventory = $BoardGameInventory
                    ->where('board_game_id', $id)
                    ->where('user_id', $user->id)->get();

                return BoardGameInventoryResource::collection($inventory);
//            });
        }
    }

    public function getStatusEffects(
        $slug,
        $name,
        BoardGame $BoardGame,
        PlayerStatusEffect $PlayerStatusEffect
    )
    {
        $user = User::findByName($name)->first();

        if ($user) {
//            $cacheKey = 'board_game_' . $slug . '_player_inventory_' . $user->id . '_cache';
//            $minutes = 60 * 24 * 30; // 30 дней
//
//            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $BoardGameInventory, $user, $slug) {
            $id = $BoardGame->findBySlug($slug)->value('id');

            $statusEffects = $PlayerStatusEffect
                ->where('board_game_id', $id)
                ->where('user_id', $user->id)->get();

            return PlayerStatusEffectResource::collection($statusEffects);
//            });
        }
    }

    /* Игры авторизованного игрока в настольной игре */
    public function getGames(
        $slug,
        $name,
        BoardGame $BoardGame,
        PlayerGame $PlayerGame
    )
    {
        $user = User::findByName($name)->first();

        if ($user) {
//            $cacheKey = 'board_game_' . $slug . '_player_games_' . $user->id . '_cache';
//            $minutes = 60 * 24 * 30; // 30 дней
//
//            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $PlayerGame, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $playerGames = PlayerGame::where('board_game_id', $id)
                    ->where('user_id', $user->id)
                    ->where('status', '!=', PlayerGame::CURRENT)
                    ->orderByDesc('id')->get();

                return PlayerGameResource::collection($playerGames);
//            });
        }
    }

    public function getCurrentGame(
        $slug,
        $name,
        BoardGame $BoardGame,
        PlayerGame $PlayerGame
    )
    {
        $user = User::findByName($name)->first();

        if ($user) {
//            $cacheKey = 'board_game_' . $slug . '_player_current_game_' . $user->id . '_cache';
//            $minutes = 60 * 24 * 30; // 30 дней
//
//            return Cache::remember($cacheKey, $minutes, function () use ($BoardGame, $PlayerGame, $user, $slug) {
                $id = $BoardGame->findBySlug($slug)->value('id');

                $playerGames = PlayerGame::where('board_game_id', $id)
                    ->where('user_id', $user->id)
                    ->where('status', '=', PlayerGame::CURRENT)
                    ->orderByDesc('id')->get();

                return PlayerGameResource::collection($playerGames);
//            });
        }
    }

    /* Устаревшие методы */

    public function get(
        $id,
        Request $request,
        BoardGamePlayer $BoardGamePlayer,
        BoardGameInventory $BoardGameInventory,
        BoardGameLog $BoardGameLog,
        BoardGamePlayerPosition $BoardGamePlayerPosition
    )
    {
        $player = $BoardGamePlayer->where('user_id', $id)->where('board_game_id', $request->board_game_id)->first();
//        $inventory = $BoardGamePlayer->inventory->where('board_game_id', $request->board_game_id);
        $inventory = $BoardGameInventory->where('user_id', $id)->where('board_game_id', $request->board_game_id)->get();
        $logs = $BoardGameLog->where('created_by', $id)->where('board_game_id', $request->board_game_id)->orderByDesc('id')->limit(100)->get();
        $steps = $BoardGamePlayerPosition->where('user_id', $id)->where('board_game_id', $request->board_game_id)->orderByDesc('id')->limit(100)->get();

        return [
            'player_info' => BoardGamePlayerFullResource::make($player),
            'inventory' => BoardGameInventoryResource::collection($inventory),
            'logs' => LogResource::collection($logs),
            'steps' => BoardGamePlayerPositionsResource::collection($steps),
        ];
    }

    public function list(Request $request, BoardGamePlayer $BoardGamePlayer)
    {
        $players = $BoardGamePlayer->where('board_game_id', $request->board_game_id)->first();

        return BoardGamePlayerResource::collection($players);
    }

    public function add(Request $request, BoardGamePlayer $BoardGamePlayer)
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'status' => 'error',
                'status_message' => 'Функционал доступен только авторизованному пользователю',
            ];
        }

        $boardGame = BoardGame::findBySlug($request->slug)->active()->first();

        if (!$boardGame) {
            return [
                'status' => 'error',
                'status_message' => 'Ивент не найден или не активен',
            ];
        }

        if ($boardGame->status === 0) {
            return [
                'status' => 'error',
                'status_message' => 'Ивент закончился',
            ];
        }

        $currentPlayer = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $boardGame->id)->first();

        // Если игрок не участвует в этом ивенте, то создаем нового игрока
        if (!$currentPlayer) {
            return PlayerGameService::joinTheGame($user, $request->slug);
        } else {
            return [
                'status' => 'error',
                'status_message' => 'Вы уже участвуете в этом ивенте',
            ];
        }
    }

    public function updatedPoints (Request $request, BoardGamePlayer $BoardGamePlayer)
    {
        $user = $request->user();

        $currentPlayer = $BoardGamePlayer->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->first();

        if ($currentPlayer) {
            $fields = [
                'points' => $request->points,
            ];

            return $currentPlayer->update($fields);
        }

        return false;
    }

    public function getDataForItemGamblingGame ($slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $items = ItemBind::active()->where('board_game_id', $conditionData['boardGame']->id)->get();

            return [
                'status' => 1,
                'items' => ItemBindResource::collection($items),
                'player' => BoardGamePlayerWithInventoryResource::make($conditionData['player']),
            ];
        }
    }

    public function rollItem($slug, $withDropChance = true)
    {
        /* Проверяем что участник может крутить предмет */
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $items = ItemBind::active()->where('board_game_id', $conditionData['boardGame']->id)->get();

            if ($withDropChance) {
                /* Формируем массив, для учета шанса дропа, элемент добавляется в массив столько раз, каков его процент шанса дропа */
                $arItemChance = [];

                foreach ($items as $item) {
                    if ($item->item->drop_chance) {
                        for ($i = 1; $i <= $item->item->drop_chance; $i++) {
                            $arItemChance[] = $item;
                        }
                    }
                }

                shuffle($arItemChance);
                $randomKey = array_rand($arItemChance);

                $randItem = $arItemChance[$randomKey];
            } else {
                /* Рандомим предмет из списка доступных */
                $randItem = $items->random();
            }

            /* Добавление в логи */
            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                'крутанул рулетку предметов и ему выпало "' . $randItem->item->name . '"',
            );

            $dontAddToInventory = false;

            if ($randItem->item->actions) {
                $actions = json_decode($randItem->item->actions);

                foreach ($actions as $action) {
                    /*
                    * Если предмет авто-применяемый, то его эффект должен быть применен и в инвентарь не добаляется
                    */
                    if (
                        $action
                        && isset($action->type, $action->target, $action->value, $action->autoUse)
                        && $action->autoUse === true
                        && $action->value
                    ) {
                        $inventoryItem = ItemService::addToInventory(
                            $conditionData['user']->id,
                            $conditionData['boardGame']->id,
                            $randItem->id,
                        );

                        /* Применяем статус эффект */
                        $useItemService = new UseItemService($conditionData);

                        $data = (object)['id' => $inventoryItem->id];

                        $useItemResult = $useItemService->useItem($data);

                        $dontAddToInventory = true;
                        break;
                    }

                    /*
                     * Если предмет накладывает статус эффект,
                     * то на игрока необходимо наложить статус эффект,
                     * а не класть предмет в инвентарь
                     */
//                    if (
//                        $action
//                        && isset($action->type, $action->target, $action->value)
//                        && $action->type === 'applyStatusEffect'
//                        && $action->target === 'current'
//                        && $action->value
//                    ) {
//                        /* Применяем статус эффект */
//                        $statusEffectObj = StatusEffect::where('slug', $action->value)->first();
//
//                        $PlayerStatusEffectFields = [
//                            'user_id' => $conditionData['user']->id,
//                            'board_game_id' => $conditionData['boardGame']->id,
//                            'status_effect_id' => $statusEffectObj->id,
//                            'created_by' => $conditionData['user']->id,
//                        ];
//
//                        PlayerStatusEffect::create($PlayerStatusEffectFields);
//                        $dontAddToInventory = true;
//                    }
                }
            }

            if (!$dontAddToInventory) {
                /* Добавление предмета в инвентарь */
                ItemService::addToInventory(
                    $conditionData['user']->id,
                    $conditionData['boardGame']->id,
                    $randItem->id,
                );
            }

            /* Уменьшаем доступное количество ролов */
            $conditionData['player']->item_roll_count--;
            $conditionData['player']->save();

            return ItemBindResource::make($randItem);
        }
    }

    public function getInteractions($slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $playerInteractions = PlayerInteractions::where('board_game_id', $conditionData['boardGame']->id)
                ->where(function($query) use ($conditionData) {
                    $query->where('created_by', '=', $conditionData['user']->id)->orWhere('with_player', '=', $conditionData['user']->id);
                })->active()->orderByDesc('id')
                ->get();

            return [
                'status' => 1,
                'interaction' => PlayerInteractionResource::collection($playerInteractions),
                'player' => BoardGamePlayerWithInventoryResource::make($conditionData['player']),
            ];
        }
    }
}
