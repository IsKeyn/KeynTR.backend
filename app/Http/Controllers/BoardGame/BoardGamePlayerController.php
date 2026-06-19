<?php

namespace App\Http\Controllers\BoardGame;

use App\Filters\BoardGame\BgPlayerFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BgShortResource;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\BoardGamePlayerFullResource;
use App\Http\Resources\BoardGame\BoardGamePlayerPositionsResource;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\BoardGamePlayerShortResource;
use App\Http\Resources\BoardGame\BoardGamePlayerWithCurrentGameResource;
use App\Http\Resources\BoardGame\BoardGamePlayerWithInventoryResource;
use App\Http\Resources\BoardGame\ItemBindResource;
use App\Http\Resources\BoardGame\Items\BgInventoryResource;
use App\Http\Resources\BoardGame\LogResource;
use App\Http\Resources\BoardGame\Player\BgPlayerDetailResource;
use App\Http\Resources\BoardGame\Player\BgPlayerListResource;
use App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameShortResource;
use App\Http\Resources\BoardGame\PlayerInteractionResource;
use App\Http\Resources\BoardGame\StatusEffects\BgPlayerStatusEffectBindResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\User;
use App\Services\BoardGame\ItemService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\UseItemService;
use App\Services\Cache\BoardGame\BgInventoryCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Cache\BoardGame\StatusEffect\BgPlayerStatusEffectCacheService;
use App\Services\Entity\DefaultEntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class BoardGamePlayerController extends Controller
{
    protected DefaultEntityService $defaultEntityService;

    public function __construct(DefaultEntityService $defaultEntityService)
    {
        $this->defaultEntityService = $defaultEntityService;
    }

    public function getPlayer (
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        if (!$slug || !$name) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $userId = User::findByName($name)->value('id');

        if (!$userId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $slug . '_' . $userId;

        return Cache::remember(
            $cacheKey,
            BgPlayerCacheService::TIME,
            function () use ($BoardGame, $slug, $BoardGamePlayer, $userId
        ) {
            $bgId = $BoardGame->findBySlug($slug)->value('id');

            if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

            $player = $BoardGamePlayer
                ->findByBoardGame($bgId)
                ->findByUserId($userId)
                ->with([
                    'user',
                    'user.avatar',
                    'user.additionalFields',
                    'positions',
                ])
                ->first();

            if (!$player) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

            return BgPlayerDetailResource::make($player);
        });
    }

    public function getCurrent( // TODO где используется?
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

    public function getList(BoardGame $boardGame, Request $request)
    {
        $cacheKey = BgPlayerCacheService::LIST_PREFIX . '_' . $boardGame->slug;

        if (!$request->fullList) {
            $cacheKey .= '_' . $request->page . '_' . $request->perPage;
        }

        $time = BgPlayerCacheService::TIME;

        if ($request->filters) {
            $cacheToken = Cache::rememberForever(
                BgPlayerCacheService::LIST_TOKEN,
                fn() => Str::random(10)
            );

            $cacheKey .= '_' . md5(json_encode($request->filters, 16)) . '_' . $cacheToken;
            $time = BgPlayerCacheService::FILTER_TIME;
        }

        return Cache::remember($cacheKey, $time, function () use ($request, $boardGame) {
            $filter = new BgPlayerFilter($request);
            $players = $filter
                ->apply(BoardGamePlayer::where('board_game_id', $boardGame->id))
                ->with([
                    'boardGame',
                    'positions',
                    'mainTimers' => function ($query) use ($boardGame) {
                        $query->where('board_game_id', $boardGame->id);
                    },
                    'user',
                    'user.avatar',
                    'currentGames',
                    'currentGames.user',
                    'currentGames.game',
                    'currentGames.comment',
                    'currentGames.game.platform',
                    'currentGames.game.addedBy',
                    'currentGames.game.game',
                    'currentGames.game.game.dates',
                    'currentGames.game.game.titleImage',
                    'currentGames.game.game.cover',
                    'currentGames.game.game.genres',
                    'statusEffects' => function ($query) {
                        $query->active()->orderBy('updated_at', 'desc');
                    },
                    'statusEffects.statusEffectBind.statusEffect.titleImage',
                    'inventory' => function ($query) {
                        $query->active()->where('has_used', false)->orderBy('created_at', 'desc');
                    },
                    'inventory.item.item',
                    'inventory.item.item.titleImage',
                    'inventory.item.item.sound',
                    'inventory.item.item.authorUser',
                ]);

            if (!isset($request->sort)) {
                $players->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $request->fullList ? $players->get() : $players->paginate($request->perPage ? $request->perPage : 10);

            return BgPlayerListResource::collection($result);
        });
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultEntityService->getListFilters(
            $request,
            BoardGamePlayer::class,
            BoardGamePlayer::FILTER,
            BoardGamePlayer::CACHE_SERVICE,
        );
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

    /*
     * Ивенты игрока
     */
    public function getEvents(
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGamePlayer $BoardGamePlayer
    )
    {
        $userId = User::findByName($name)->value('id');
        if (!$userId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $bgId = $BoardGame->findBySlug($slug)->value('id');
        if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BoardGameCacheService::LIST_PREFIX . '_' . $slug . '_' . $userId;

        return Cache::remember($cacheKey, BoardGameCacheService::TIME, function () use ($BoardGamePlayer, $userId, $bgId) {
            $playerInGames = $BoardGamePlayer
                ->findByUserId($userId)
                ->where('board_game_id', '!=', $bgId)
                ->with(['boardGame', 'boardGame.media'])
                ->get();

            $boardGames = collect([]);

            foreach ($playerInGames as $player) {
                if ($player->boardGame && !$player->boardGame->is_close) {
                    $boardGames->push($player->boardGame);
                }
            }

            return BgShortResource::collection($boardGames);
        });
    }

    /*
     * Инвентарь игрока
     */
    public function getInventory(
        $slug,
        $name,
        BoardGame $BoardGame,
        BoardGameInventory $BoardGameInventory
    )
    {
        $userId = User::findByName($name)->value('id');
        if (!$userId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $bgId = $BoardGame->findBySlug($slug)->value('id');
        if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgInventoryCacheService::LIST_PREFIX . '_' . $slug . '_' . $userId;

        return Cache::remember($cacheKey, BgInventoryCacheService::TIME, function () use ($BoardGameInventory, $userId, $bgId) {
            $inventory = $BoardGameInventory
                ->where('board_game_id', $bgId)
                ->where('user_id', $userId)
                ->with([
                    'item.item',
                    'item.item.titleImage',
                    'item.item.sound',
                    'item.item.authorUser',
                ])
                ->get();

            return BgInventoryResource::collection($inventory);
        });
    }

    /*
     * Статус эффекты игрока
     */
    public function getStatusEffects(
        $slug,
        $name,
        BoardGame $BoardGame,
        PlayerStatusEffect $PlayerStatusEffect
    )
    {
        $userId = User::findByName($name)->value('id');
        if (!$userId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $bgId = $BoardGame->findBySlug($slug)->value('id');
        if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerStatusEffectCacheService::LIST_PREFIX . '_' . $slug . '_' . $userId;

        return Cache::remember($cacheKey, BgPlayerStatusEffectCacheService::TIME,
            function () use ($BoardGame, $PlayerStatusEffect, $userId, $bgId) {
            $statusEffects = $PlayerStatusEffect
                ->where('board_game_id', $bgId)
                ->where('user_id', $userId)
                ->with([
                    'statusEffect.titleImage',
                ])
                ->get();

            return BgPlayerStatusEffectBindResource::collection($statusEffects);
        });
    }

    /*
     * Список игр игрока
     */
    public function getGames(
        Request $request,
        $slug,
        $name,
        BoardGame $BoardGame
    )
    {
        $user = User::findByName($name)->first();
        if (!$user) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $bgId = $BoardGame->findBySlug($slug)->value('id');
        if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerGameCacheService::LIST_PREFIX . '_' . $slug . '_' . $user->id;

        if (!$request->fullList) {
            $cacheKey .= '_' . $request->page . '_' . $request->perPage;
        }

        return Cache::remember($cacheKey, BgPlayerGameCacheService::TIME, function () use ($request, $BoardGame, $user, $bgId) {
            $playerGames = PlayerGame::where('board_game_id', $bgId)
                ->where('user_id', $user->id)
                ->where('status', '!=', PlayerGame::CURRENT)
                ->with([
                    'user',
                    'user.avatar',
                    'game.platform',
                    'game.addedBy',
                    'game.game',
                    'game.game.dates',
                    'game.game.titleImage',
                    'game.game.cover',
                    'game.game.genres',
                ])
                ->orderByDesc('id');

            $result = $request->fullList ? $playerGames->get() : $playerGames->paginate($request->perPage ? $request->perPage : 10);

            $resourceCollection = BgPlayerGameShortResource::collection($result);
            $standardResponse = $resourceCollection->response()->getData(true);

            $playerGamesList = PlayerGame::where('board_game_id', $bgId)
                ->where('user_id', $user->id)
                ->where('status', '!=', PlayerGame::CURRENT)
                ->select(['id', 'status'])
                ->orderByDesc('id')
                ->get();

            $finalResponse = array_merge($standardResponse, [
                'data_for_chart' => [
                    $playerGamesList->where('status', PlayerGame::COMPLETED)->count(),
                    $playerGamesList->where('status', PlayerGame::REROLLED)->count(),
                    $playerGamesList->where('status', PlayerGame::GIVEN_AWAY)->count(),
                ],
            ]);

            return response()->json($finalResponse);
        });
    }

    public function getCurrentGame(
        $slug,
        $name,
        BoardGame $BoardGame,
        PlayerGame $PlayerGame
    )
    {
        $user = User::findByName($name)->first();
        if (!$user) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $bgId = $BoardGame->findBySlug($slug)->value('id');
        if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgPlayerGameCacheService::DETAIL_PREFIX . '_current_' . $slug . '_' . $user->id;

        return Cache::remember($cacheKey, BgPlayerGameCacheService::TIME, function () use ($BoardGame, $PlayerGame, $user, $slug) {
            $bgId = $BoardGame->findBySlug($slug)->value('id');

            $playerGames = $PlayerGame::where('board_game_id', $bgId)
                ->where('user_id', $user->id)
                ->where('status', '=', PlayerGame::CURRENT)
                ->with([
                    'user',
                    'user.avatar',
                    'game.platform',
                    'game.addedBy',
                    'game.game',
                    'game.game.dates',
                    'game.game.titleImage',
                    'game.game.cover',
                    'game.game.genres',
                ])
                ->orderByDesc('id')
                ->get();

            return BgPlayerGameShortResource::collection($playerGames);
        });
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
