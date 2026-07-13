<?php

namespace App\Http\Controllers\BoardGame;

use App\Filters\BoardGame\BgPlayerFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BgShortResource;
use App\Http\Resources\BoardGame\Board\BgPlayerInteractionResource;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Http\Resources\BoardGame\BoardGamePlayerFullResource;
use App\Http\Resources\BoardGame\BoardGamePlayerPositionsResource;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\BoardGamePlayerWithCurrentGameResource;
use App\Http\Resources\BoardGame\Items\BgInventoryResource;
use App\Http\Resources\BoardGame\Items\BgItemBindResource;
use App\Http\Resources\BoardGame\LogResource;
use App\Http\Resources\BoardGame\Player\BgPlayerDetailResource;
use App\Http\Resources\BoardGame\Player\BgPlayerListResource;
use App\Http\Resources\BoardGame\PlayerGame\BgPlayerGameShortResource;
use App\Http\Resources\BoardGame\StatusEffects\BgPlayerStatusEffectResource;
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
use App\Services\BoardGame\BgPlayerService;
use App\Services\BoardGame\ItemService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\UseItemService;
use App\Services\Cache\BoardGame\BgInventoryCacheService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use App\Services\Cache\BoardGame\BgPlayerInteractionCacheService;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Cache\BoardGame\StatusEffect\BgPlayerStatusEffectCacheService;
use App\Services\Entity\DefaultEntityService;
use App\Services\MediaService;
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
                    'positions' => function ($query) {
                        $query->active()->orderBy('id', 'desc');
                    },
                    'media' => function ($query) {
                        $query->wherePivot('type', BoardGamePlayer::MEDIA_BG_IMAGE);
                    },
                ])
                ->first();

            if (!$player) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

            return BgPlayerDetailResource::make($player);
        });
    }

    public function getPlayerWithInventory(Request $request)
    {
        return BgPlayerService::getPlayerWithInventoryById($request->player_id);
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
                    'positions' => function ($query) {
                        $query->active()->orderBy('id', 'desc');
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
                    'inventory.itemBind.item',
                    'inventory.itemBind.item.titleImage',
                    'inventory.itemBind.item.sound',
                    'inventory.itemBind.item.authorUser',
                    'media' => function ($query) {
                        $query->wherePivot('type', BoardGamePlayer::MEDIA_BG_IMAGE);
                    },
                ]);

            if (!isset($request->sort)) {
                $players->orderByRaw('sort IS NULL, sort ASC');
            }

            $filters = json_decode($request->filters, true) ?? [];

            if ($request->fullList) {
                $result = $players->get();
            } elseif ($request->filters && isset($filters['limit']) && $filters['limit']) {
                $result = $players->paginate($filters['limit']);
            } else {
                $result = $players->paginate($request->perPage ? $request->perPage : 10);
            }

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
        BoardGame $BoardGame
    )
    {
        if (!in_array($request->type, ['battleForPoints', 'inviteToCoop'], true)) {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $boardGame = $BoardGame
            ->findBySlug($slug)
            ->with([
                'players',
                'players.positions',
                'players.user',
                'players.user.avatar',
            ])
            ->first();

        if (!$boardGame) {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $players = $boardGame->players;

        $playerInteractionsIds = PlayerInteractions::query()
            ->findByBoardGame($boardGame->id)
            ->where('created_by', $user->id)
            ->where('type', $request->type)
            ->whereIn('status', [
                PlayerInteractions::COOP_FINISH,
                PlayerInteractions::I_WIN,
                PlayerInteractions::I_LOSE
            ])
            ->pluck('with_player')
            ->unique()
            ->toArray();

        $players = $players->whereNotIn('user_id', $playerInteractionsIds);

        return BgPlayerDetailResource::collection($players);
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

    /**
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
                    'itemBind.item',
                    'itemBind.item.titleImage',
                    'itemBind.item.sound',
                    'itemBind.item.authorUser',
                ])
                ->get();

            return BgInventoryResource::collection($inventory);
        });
    }

    /**
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
                    'statusEffectBind',
                    'statusEffectBind.statusEffect',
                    'statusEffectBind.statusEffect.titleImage',
                ])
                ->get();

            return BgPlayerStatusEffectResource::collection($statusEffects);
        });
    }

    /**
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
                    'comment',
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

    public function getDataForItemGamblingGame($slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        return [
            'items' => ItemService::itemsInBoardGame($conditionData['boardGame']->id),
            'player' => BgPlayerService::getPlayerWithInventory($conditionData['player']),
        ];
    }

    public function rollItem($slug, $withDropChance = true)
    {
        /* Проверяем что участник может крутить предмет */
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        if ($conditionData['player']->item_roll_count <= 0) {
            return [
                'status' => 'error',
                'status_message' => __('boardGame.player.dont_have_items_roll')
            ];
        }

        $items = ItemBind::query()
            ->findByBoardGame($conditionData['boardGame']->id)
            ->active()
            ->with([
                'item',
                'item.titleImage',
                'item.sound',
                'item.authorUser',
            ])
            ->get();

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
            $conditionData['player']->id,
        );

        $dontAddToInventory = false;

        if ($randItem->item->actions) {
            $actions = $randItem->item->actions;

            foreach ($actions as $action) {
                $action = (object) $action; // Для корректной работы легаси кода

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
                        $conditionData['player']->id,
                    );

                    /* Применяем статус эффект */
                    $useItemService = new UseItemService($conditionData);

                    $data = (object)['id' => $inventoryItem->id];

                    $useItemService->useItem($data);

                    $dontAddToInventory = true;
                    break;
                }
            }
        }

        if (!$dontAddToInventory) {
            /* Добавление предмета в инвентарь */
            ItemService::addToInventory(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                $randItem->id,
                $conditionData['player']->id,
            );
        }

        /* Уменьшаем доступное количество ролов */
        $conditionData['player']->item_roll_count--;
        $conditionData['player']->save();

        return BgItemBindResource::make($randItem);
    }

    /**
     * Взаимодействия игрока в ивенте
     *
     * @param Request $request
     * @param $slug
     * @return array|\Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|string[]
     */
    public function getInteractions(Request $request, $slug)
    {
        $bgId = null;
        $userId = null;

        if (!$slug) {
            return response()
                ->json(['error' => __('boardGame.not_received_slug')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if ($request->boolean('checkCondition')) {
            $conditionData = PlayerGameService::checkConditions($slug);

            if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
                return $conditionData;
            }

            $bgId = $conditionData['boardGame']->id;
            $userId = $conditionData['user']->id;
        } else {
            if (!$request->userId) {
                return response()
                    ->json(['error' => __('notReceived.not_received_user_id')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            $bgId = BoardGame::findBySlug($slug)->active()->value('id');
            $userId = $request->userId;
        }

        $cacheKey = BgPlayerInteractionCacheService::LIST_PREFIX . '_' . $slug . '_' . $userId . '_' . $request->active;

        return Cache::remember($cacheKey, BgPlayerInteractionCacheService::TIME, function () use ($request, $bgId, $userId) {
            $playerInteractions = PlayerInteractions::query()
                ->findByBoardGame($bgId)
                ->where(function ($query) use ($userId) {
                    $query
                        ->where('created_by', '=', $userId)
                        ->orWhere('with_player', '=', $userId);
                })
                ->orderByDesc('id')
                ->with([
                    'withPlayerData',
                    'withPlayerData.avatar',
                    'createdByData',
                    'createdByData.avatar',
                ]);

            if ($request->boolean('active')) {
                $playerInteractions->active();
            }

            $result = $playerInteractions->get();

            return BgPlayerInteractionResource::collection($result);
        });
    }

    /**
     * Функция сохраняет настройку пользователя по паре settingName, settingValue
     * которые берутся из $request
     *
     * @param Request $request
     * @param $slug String Slug настольной игры
     * @return array|\Illuminate\Http\JsonResponse|string[]
     */
    public function setSetting(Request $request, $slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        if (!$request->settingName) {
            return response()
                ->json(['error' => __('boardGame.player.settings.dont_received_setting_name')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if ($request->settingName === 'exceptionPlatforms' && count($request->settingValue) > 3) {
            return response()
                ->json(['error' => __('boardGame.player.settings.to_many_exception_platforms')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $player = $conditionData['player'];
        $settings = $player->settings ?? [];
        $settings[$request->settingName] = $request->settingValue ?? null;
        $player->settings = $settings;

        if ($player->save()) {
            return response()
                ->json(['message' => __('actions.success_save')])
                ->setStatusCode(Response::HTTP_OK);
        }

        return response()
            ->json(['error' => __('actions.failed_save')])
            ->setStatusCode(Response::HTTP_BAD_REQUEST);
    }

    public function setPlayerBackground(Request $request, $slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $mediaService = new MediaService();

        $conditionData['player']->load([
            'media' => function ($query) {
                $query->wherePivot('type', BoardGamePlayer::MEDIA_BG_IMAGE);
            },
        ]);

        $bgImage = $conditionData['player']->media->first();

        if ($bgImage) {
            $mediaService->destroy($bgImage);
            $conditionData['player']->media()->detach();
        }

        $playerName = $conditionData['user']->public_name ? $conditionData['user']->public_name : $conditionData['user']->name;

       $fileArray = [
           'name' => 'Бекграунд изображение игрока ' . $playerName . ' в ивенте ' . $conditionData['boardGame']->name,
           'src' => $request->file('bgImage'),
       ];

       if ($bgImage = $mediaService->addMedia($fileArray, $conditionData['user'])) {
           $conditionData['player']->media()->syncWithPivotValues($bgImage->id, ['type' => 1]);

           $conditionData['player']->touch();
       }

       return $bgImage;
    }

    public function setPlayerSettings(Request $request, $slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $player = $conditionData['player'];

        if ($request->name) {
            $settings = $player->settings ?? [];
            $settings[$request->name] = $request->value;
            $player->settings = $settings;
            $player->save();
        }

        return response()->json(['message' => 'Настройки обновлены']);
    }
}
