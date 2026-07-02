<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\Board\BgPlayerInteractionResource;
use App\Http\Resources\BoardGame\GameListResource;
use App\Http\Resources\BoardGame\Games\BgGameRouletteListResource;
use App\Http\Resources\BoardGame\Player\BgPlayerWithCurrentGameResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\GamingPlatform;
use App\Models\User;
use App\Services\BoardGame\ActionsService;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\InteractionsService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\TimerService;
use App\Services\CommentService;
use App\Services\ErrorService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlayerGameController extends Controller
{
    /**
     * @param Request $request
     * @param $slug
     * @return array|string[]
     */
    public function getPlayerList(
        Request $request,
        $slug
    )
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $coopInteractions = PlayerInteractions::query()
            ->findByBoardGame($conditionData['boardGame']->id)
            ->where('created_by', $conditionData['user']->id)
            ->where('type', 'inviteToCoop')
            ->whereIn('status', [PlayerInteractions::STATUS_ACTIVE, PlayerInteractions::STATUS_ACCEPTED])
            ->with([
                'withPlayerData',
                'withPlayerData.avatar',
                'createdByData',
                'createdByData.avatar',
            ])
            ->active()
            ->get();

        $conditionData['player']
            ->load([
                'positions',
                'currentGames',
                'currentGames.game',
                'currentGames.game.platform',
                'currentGames.game.addedBy',
                'currentGames.game.game',
                'currentGames.game.game.dates',
                'currentGames.game.game.titleImage',
                'currentGames.game.game.cover',
                'currentGames.game.game.genres',
                'currentGames.boardGame',
                'user',
                'user.avatar',
                'mainTimers' => function ($query) use ($conditionData) {
                    $query->where('board_game_id', $conditionData['boardGame']->id)->orderBy('id', 'desc');
                },
            ]);

        // Если есть текущая игра, то возвращаем её
        if ($conditionData['player']->currentGames->first()) {
            return [
                'status' => 1,
                'coopInteraction' => BgPlayerInteractionResource::collection($coopInteractions),
                'player' => BgPlayerWithCurrentGameResource::make($conditionData['player']),
            ];
        }

        // Проверяем статус эффекты и при необходимости устанавливаем платформу фильтрации
        $playerStatusEffects = PlayerStatusEffect::query()
            ->findByUserId($conditionData['user']->id)
            ->findByBoardGame($conditionData['boardGame']->id)
            ->with([
                'statusEffectBind.statusEffect'
            ])
            ->active()
            ->get();

        $platformSlug = null;

        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                foreach (json_decode($statusEffect->statusEffectBind->statusEffect->actions) as $action) {
                    if (isset($action->type) && $action->type === 'platform' && $action->value) {
                        $platformSlug = $action->value;
                    }
                }

                if ($platformSlug) break;
            }
        }

        if ($platformSlug) {
            $platformId = GamingPlatform::findBySlug($platformSlug)->value('id');
        } else {
            $platformId = $request->platform_id ? $request->platform_id : null;
        }

        $games = $this->getFilteredGameList($platformId, $conditionData);

        return [
            'status' => 1,
            'coopInteraction' => BgPlayerInteractionResource::collection($coopInteractions),
            'games' => isset($games['gameList']) ? BgGameRouletteListResource::collection($games['gameList']) : null,
            'listType' => isset($games['listType']) ? $games['listType'] : null,
            'player' => BgPlayerWithCurrentGameResource::make($conditionData['player']),
        ];
    }

    public function add(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $fields = array_merge(
                [
                    'user_id' => $conditionData['user']->id,
                    'board_game_game_list_id' => $request->board_game_game_list_id,
                    'board_game_id' => $conditionData['boardGame']->id,
                    'created_by' => $conditionData['user']->id,
                ],
                $this->getFields($request),
            );

            $game = PlayerGame::create($fields);

            if ($game) {
                $message = 'отметил игру ' . $game->game->name . ' как ';

                if ($request->type === PlayerGame::CURRENT) {
                    $message .= 'текущую';
                } else if ($request->type === PlayerGame::REROLLED) {
                    $message .= 'рерольнутую';
                } else if ($request->type === PlayerGame::COMPLETED) {
                    $message .= 'пройденную';
                } else if ($request->type === PlayerGame::GIVEN_AWAY) {
                    $message .= 'отданную';
                }

                if ($message) {
                    if ($request->comment) {
                        $message .= ' и оставил мнение об игре: "' . $request->comment . '"';
                    }

                    LogService::addLog(
                        $conditionData['user']->id,
                        $conditionData['boardGame']->id,
                        $message,
                        $conditionData['player']->id,
                    );
                }
            }

            return $game;
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse|string[]
     */
    public function update(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $conditionData['boardGame']->load([
            'settings'
        ]);

        $conditionData['player']->load([
            'currentGames',
            'currentGames.game',
            'currentGames.game.addedBy',
            'currentGames.game.game',
            'mainTimers' => function ($query) use ($conditionData) {
                $query->where('board_game_id', $conditionData['boardGame']->id)->orderBy('id', 'desc');
            },
        ]);

        $playerCurrentGame = $conditionData['player']->currentGames->first();

        // Проверяем статус таймера и если таймер истек, не выполнять действий с игрой
        $status = TimerService::getTimerStatus($conditionData['player']->mainTimers->first());

        if ($playerCurrentGame->type !== PlayerGame::TYPE_TAKEN && $status && ($status['reached_the_limit'] ?? null)) {
            return ErrorService::message('Вы не можете это сделать, так как достигли лимита таймера');
        }

        if (!$playerCurrentGame) {
            return ErrorService::message('Не найдено текущей игры');
        }

        $fields = $this->getFields($request);
        $fields['finished_at'] = Carbon::now();

        try {
            $result = DB::transaction(function () use ($request, $conditionData, $playerCurrentGame, $fields) {

                if ($result = $playerCurrentGame->update($fields)) {
                    // Рерол игры
                    if ($request->type === PlayerGame::REROLLED) {
                        // Добавление предмета при рероле
//                    $fields = $request->validate([
//                        'board_game_id' => 'required',
//                    ]);
//
//                    $boardGameItems = ItemBind::query()->where('slug', 'tuhlyi-banan')->first();
//
//                    $fields['board_game_item_id'] = $boardGameItems->id;
//                    $fields['user_id'] = $user->id;
//                    $fields['created_by'] = $user->id;
//
//                    BoardGameInventory::create($fields);

                        // Проверяем статус эффекты и при необходимости делем реролл без штрафов
                        $playerStatusEffects = PlayerStatusEffect::query()
                            ->findByUserId($conditionData['user']->id)
                            ->findByBoardGame($conditionData['boardGame']->id)
                            ->with([
                                'statusEffectBind.statusEffect',
                                'statusEffectBind.statusEffect.titleImage',
                            ])
                            ->active()
                            ->get();

                        $freeReroll = false;

                        foreach ($playerStatusEffects as $statusEffect) {
                            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                                foreach (json_decode($statusEffect->statusEffectBind->statusEffect->actions) as $action) {
                                    if ($action->value && $action->value === 'free-reroll') {
                                        $freeReroll = true;
                                    }
                                }

                                if ($freeReroll) {
                                    $statusEffect->update(['active' => false]);
                                    break;
                                }
                            }
                        }

                        $subtractPointsCount = GameService::rerollPenalty($conditionData['boardGame'],
                            $playerCurrentGame);

                        if (!$freeReroll) {
                            // Отнимает очки при рероле и сбрасываем стрик при рероле
                            $conditionData['player']->points = $conditionData['player']->points - (int)$subtractPointsCount['pointForReroll'];
                            $conditionData['player']->streak = 0;

                            // Если игрок рерольнул свою игру, то обновляем счетчик своих рерольнутых игр
                            if ($playerCurrentGame->game->addedBy === $conditionData['player']->user_id) {
                                $conditionData['player']->rerolled_own_game_count = $conditionData['player']->rerolled_own_game_count + 1;
                            }

                            $conditionData['player']->save();
                        }

                        // Игра из очереди
                        $this->gameFromQueue($conditionData);

                        $message = 'рерольнул игру ' . $playerCurrentGame->game->game->name . ' и потерял ' . $subtractPointsCount['pointForReroll'] . ' очков';

                        if ($freeReroll && isset($subtractPointsCount['data']) && $subtractPointsCount['data']->name) {
                            $message .= ', так как защищен "' . $subtractPointsCount['data']->name . '"';
                        }

                        // Возвращаем предмет, если была рерольнута отданная игра
                        if ($playerCurrentGame->from_user_id) {
                            // Получаем взаимодействие

                            $conditionData['player']->load([
                                'playerInteractions' => function ($query) use ($conditionData, $playerCurrentGame) {
                                    $query
                                        ->where('type', 'playForMe')
                                        ->where('status', PlayerInteractions::STATUS_ACCEPTED)
                                        ->findByBoardGame($conditionData['boardGame']->id)
                                        ->where('with_player', $conditionData['player']->user_id)
                                        ->where('created_by', $playerCurrentGame->from_user_id)
                                        ->orderByDesc('id');
                                },
                                'playerInteractions.entity',
                            ]);

                            $playerInteraction = $conditionData['player']->playerInteractions->first();

                            if ($entity = $playerInteraction->entity) {
                                $entity->has_used = false;
                                $entity->save();

                                // Добавляем лог и уведомление
                                LogService::addLog(
                                    $playerCurrentGame->from_user_id,
                                    $conditionData['boardGame']->id,
                                    'получил назад предмет ' . $entity->item->name . ', так как переданная им игра ' . $playerCurrentGame->game->game->name . ' была рерольнута',
                                    $conditionData['player']->id,
                                );

                                NotificationService::set(
                                    [
                                        'user_id' => $playerCurrentGame->from_user_id,
                                        'message' => 'предмет ' . $entity->item->name . ', снова у вас в инвентаре, так как переданная им игра ' . $playerCurrentGame->game->game->name . ' была рерольнута',
                                    ]
                                );
                            }
                        }

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра пройдена */
                    if ($request->type === PlayerGame::COMPLETED) {
                        /* Рассчитываем количество очков за игру */
                        $pointsForGame = GameService::calcPoints($playerCurrentGame->game);

                        if ($playerCurrentGame->type === PlayerGame::TYPE_TAKEN) {
                            $pointsForGame = round($pointsForGame / 2);

                            // Даем очки, игроку, который передал игру
                            if ($playerCurrentGame->from_user_id) {
                                // Получаем игрока
                                $playerFrom = BoardGamePlayer::query()
                                    ->findByBoardGame($conditionData['boardGame']->id)
                                    ->findByUserId($playerCurrentGame->from_user_id)
                                    ->active()
                                    ->first();

                                // Добавляем очки
                                if ($playerFrom) {
                                    $playerFrom->points = $playerFrom->points + $pointsForGame;
                                    $playerFrom->save();

                                    LogService::addLog(
                                        $playerCurrentGame->from_user_id,
                                        $conditionData['boardGame']->id,
                                        'получил ' . $pointsForGame . ' очков за отданную игру ' . $playerCurrentGame->game->game->name,
                                        $conditionData['player']->id
                                    );
                                }
                            }
                        }

                        if ($playerCurrentGame->type === PlayerGame::TYPE_PURSE) {
                            // Даем очки, игроку, который передал игру
                            if ($playerCurrentGame->from_user_id) {
                                // Получаем игрока
                                $playerFrom = BoardGamePlayer::query()
                                    ->findByBoardGame($conditionData['boardGame']->id)
                                    ->findByUserId($playerCurrentGame->from_user_id)
                                    ->active()
                                    ->first();

                                // Добавляем очки
                                if ($playerFrom) {
                                    $playerFrom->points = $playerFrom->points + round($pointsForGame / 2);
                                    $playerFrom->save();

                                    LogService::addLog(
                                        $playerCurrentGame->from_user_id,
                                        $conditionData['boardGame']->id,
                                        'получил ' . round($pointsForGame / 2) . ' очков за переданную мошной игру ' . $playerCurrentGame->game->game->name,
                                        $conditionData['player']->id,
                                    );
                                }
                            }
                        }

                        // Добавляем очки за стрик
                        $finalPoints = $conditionData['player']->streak > 0 ? $pointsForGame + ($pointsForGame / 100 * ($conditionData['player']->streak * 2)) : $pointsForGame;

                        // Тихо обновляем очки игрока, чтобы не вызывать событие, оно уже было вызвано вверху метода
                        $playerCurrentGame->points = $finalPoints;
                        $playerCurrentGame->saveQuietly();

                        $conditionData['player']->points = $conditionData['player']->points + $finalPoints;

                        /* Добавляем стрик, если он не достиг максимального */
                        $maxStreakSetting = $conditionData['boardGame']->settings->where('code', '=',
                            'max_string')->first();
                        $maxStreak = $maxStreakSetting ? $maxStreakSetting->value : 5;

                        if ($conditionData['player']->streak < $maxStreak && $conditionData['player']->streak !== $maxStreak) {
                            $conditionData['player']->streak++;
                        }

                        /* Добавляем ролл предметы и добавляем ходы */
                        $conditionData['player']->item_roll_count = $conditionData['player']->item_roll_count + 1;
                        $conditionData['player']->step_count = $conditionData['player']->step_count + 1;

                        $conditionData['player']->save();

                        // Игра из очереди
                        $this->gameFromQueue($conditionData);

                        $message = 'прошел игру ' . $playerCurrentGame->game->game->name . ' и получил за неё ' . $pointsForGame . ' очков';

                        if ($request->time) {
                            $formattedTime = sprintf("%02d:%02d:%02d",
                                floor($request->time / 3600),
                                floor(($request->time % 3600) / 60),
                                $request->time % 60
                            );

                            if ($formattedTime) {
                                $message .= ', затратил ' . $formattedTime;
                            }
                        }

                        // Проверяем, есть ли активное и принятое приглашение в кооп от этого игрока и есть есть, то добавляем напарнику по коопу очки
                        $conditionData['player']->load([
                            'playerInteractions' => function ($query) use ($conditionData) {
                                $query
                                    ->where('type', 'inviteToCoop')
                                    ->where('status', PlayerInteractions::STATUS_ACCEPTED)
                                    ->where('created_by', $conditionData['user']->id);
                            },
                            'playerInteractions.entity',
                            'playerInteractions.withPlayerData' => function ($query) use ($conditionData) {
                                $query
                                    ->findByBoardGame($conditionData['boardGame']->id)
                                    ->active();
                            },
                        ]);


                        $coopInteraction = $conditionData['player']->playerInteractions->first();

                        if ($coopInteraction && $playerCurrentGame->game->coop) {
                            $player = $coopInteraction->withPlayerData->first();

                            if ($player) {
                                $player->points = $player->points + round($pointsForGame / 2);
                                $player->save();

                                $coopInteraction->active = false;
                                $coopInteraction->status = PlayerInteractions::COOP_FINISH;
                                $coopInteraction->save();

                                NotificationService::set(
                                    [
                                        'user_id' => $player->user->id,
                                        'message' => 'За помощь в прохождении игры ' . $playerCurrentGame->game->game->name . ' вы получаете ' . round($pointsForGame / 2) . ' очков'
                                    ]
                                );

                                LogService::addLog(
                                    $player->user_id,
                                    $conditionData['boardGame']->id,
                                    'получил ' . round($pointsForGame / 2) . ' за помощь в прохождении игры ' . $playerCurrentGame->game->game->name,
                                    $player->id,
                                );
                            }
                        }

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра отдана */
                    /* TODO Легаси, сейчас игра передается предметом, удалить */
                    if ($request->type === PlayerGame::GIVEN_AWAY) {
                        $message = 'отдал игру ' . $playerCurrentGame->game->game->name;
                    }

                    if ($message) {
                        if ($request->comment) {
                            $message .= ' и оставил мнение об игре: "' . $request->comment . '"';
                        }

                        LogService::addLog(
                            $conditionData['user']->id,
                            $conditionData['boardGame']->id,
                            $message,
                            $conditionData['player']->id,
                        );
                    }

                    // Возвращаем результат, который пойдет в ответ клиенту
                    return [
                        'success' => true,
                        'data' => $playerCurrentGame, // Или то, что вам нужно вернуть
                        'message' => $message
                    ];
                }
            });

            // Если мы здесь, значит транзакция успешно завершена (commit)
            return response()->json($result);
        } catch (Throwable $e) {
            // Если произошла ошибка, транзакция автоматически откатится (rollback)
            // Логируем ошибку
            Log::error('Ошибка при обновлении игры: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $conditionData['user']->id ?? null,
            ]);

            return ErrorService::message('Произошла ошибка при сохранении данных. Обратитесь к администратору.');
        }
    }

    private function gameFromQueue($conditionData)
    {
        /* Если есть игра в очереди, то делаем её текущей */
        $playerCurrentGameInQueue = PlayerGame::where('board_game_id', $conditionData['boardGame']->id)
            ->where('user_id', $conditionData['user']->id)
            ->where('status', PlayerGame::QUEUE)
            ->first();

        if ($playerCurrentGameInQueue) {
            $playerCurrentGameInQueue->status = PlayerGame::CURRENT;
            $playerCurrentGameInQueue->save();

            NotificationService::set(
                [
                    'user_id' => $conditionData['user']->id,
                    'message' => 'Игра ' . $playerCurrentGameInQueue->game->game->name . ' установлена как текущая, так как была в очереди',
                    'entity_type' => $conditionData['boardGame']->model,
                    'entity_id' => $conditionData['boardGame']->id,
                ]
            );
            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                'Теперь проходит игру ' . $playerCurrentGameInQueue->game->game->name
            );
        }
    }

    public function inviteToCoop(Request $request)
    {
        if ($request->slug) {
            $conditionData = PlayerGameService::checkConditions($request->slug);

            if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
                return $conditionData;
            } else {
                $boardGameGameList = BoardGameGameList::findById($request->board_game_game_list_id)->first();

                $action = (Object)[
                    'type' => 'playerInteractions',
                    'target' => 'other',
                    'value' => 'inviteToCoop',
                    'description' => 'Приглашение пройти в коопе игру ' . $boardGameGameList->game->name,
                ];

                $actionService = new ActionsService($conditionData, 'interactions', null);
                return $actionService->activateAction($request, $action);
            }
        } else {
            return ErrorService::message('Не получен slug');
        }
    }

    public function getFields($request)
    {
        $fields = [
            'status' => $request->type,
            'time' => $request->time,
        ];

        if ($request->comment) {
            $newComment = [
                'message' => $request->comment,
                'entity_type' => $request->entity_type,
                'entity_id' => $request->entity_id,
            ];

            $comment = CommentService::addComment($request, $newComment);

            $fields['comment_id'] = $comment->original->id;
        }

        return $fields;
    }

    /**
     * Функция отвечает за выбор игры, при крутке рулетки
     * @param $slug
     * @param Request $request
     * @param PlayerGame $playerGame
     * @return GameListResource|array|string[]
     */
    public function roll(
        $slug,
        Request $request,
        PlayerGame $playerGame
    ) {
        $conditionData = PlayerGameService::checkConditions($slug);

        // Проверяем, что игрок может крутить рулетку игр
        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $conditionData['player']
            ->load([
                'mainTimers' => function ($query) use ($conditionData) {
                    $query->where('board_game_id', $conditionData['boardGame']->id)->orderBy('id', 'desc');
                },
                'statusEffects' => function($query) {
                    $query->active();
                },
                'statusEffects.statusEffectBind',
                'statusEffects.statusEffectBind.statusEffect',
                'currentGames',
            ]);

        $status = TimerService::getTimerStatus($conditionData['player']->mainTimers->first());

        // Проверяем, не превысил ли игрок таймер
        if ($status && ($status['reached_the_limit'] ?? null)) {
            return [
                'status' => 'error',
                'status_message' => 'Вы не можете крутить игру, так как исчерпали время таймера',
            ];
        }

        // Проверяем использовал ли игрок доступные крутки предметов и доступные ходы
        if ((!$conditionData['player']->finishBoard && $conditionData['player']->step_count > 0)
            || $conditionData['player']->item_roll_count > 0) {
            return [
                'status' => 'error',
                'status_message' => 'Перед круткой рулетки игр вы должны использовать доступные крутки рулетки предметов, а такж использовать доступные ходы на игровом поле',
            ];
        }

        // Проверяем статус эффекты и при необходимости устанавливаем платформу фильтрации
        $playerStatusEffects = $conditionData['player']->statusEffects;

        $platformSlug = null;

        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                foreach (json_decode($statusEffect->statusEffectBind->statusEffect->actions) as $action) {
                    if (isset($action->type) && $action->type === 'platform' && $action->value) {
                        $platformSlug = $action->value;
                    }
                }

                if ($platformSlug) {
                    $statusEffect->update(['active' => false]);
                    break;
                }
            }
        }

        if ($platformSlug) {
            $platformId = GamingPlatform::findBySlug($platformSlug)->value('id');
        } else {
            $platformId = $request->platform_id ? $request->platform_id : null;
        }

        $gameListFiltered = $this->getFilteredGameList($platformId, $conditionData);

        if (isset($gameListFiltered['gameList']) && $gameListFiltered['gameList']->count() === 0) {
            return ErrorService::message('У вас не осталось игр, для рулетки');
        }

        $randomGame = $gameListFiltered['gameList']->random();

        if (!$randomGame) {
            return ErrorService::message('Ошибка выбора игры');
        }

        // Если у игрока есть текущая игра, отмечаем её как рерольнутую
        $currentGame = $conditionData['player']->currentGames->first();

        if ($currentGame) {
            $currentGame->update(['status' => PlayerGame::REROLLED]);
        }

        // Создаем новую текущую игру
        $fields = [
            'bg_player_id' => $conditionData['player']->id,
            'user_id' => $conditionData['user']->id,
            'status' => PlayerGame::CURRENT,
            'board_game_game_list_id' => $randomGame->id,
            'board_game_id' => $conditionData['boardGame']->id,
            'created_by' => $conditionData['user']->id,
        ];

        if (!$playerGame::create($fields)) {
            return ErrorService::message('Ошибка создания новой текущей игры');
        }

        // Если игра была из списка рерольнутых, то сбрасывает счетчик собственных рерольнутых игр
        if ($gameListFiltered['listType'] === 'rerolled' && $conditionData['player']->rerolled_own_game_count >= 2) {
            $conditionData['player']->rerolled_own_game_count = 0;
            $conditionData['player']->save();
        }

        LogService::addLog(
            $conditionData['user']->id,
            $conditionData['boardGame']->id,
            'Крутанул рулетку и выбил игру ' . $randomGame->game->name,
            $conditionData['player']->id
        );

        return GameListResource::make($randomGame);
    }

    /**
     * @param $platformId
     * @param $conditionData
     * @return array
     */
    private function getFilteredGameList(
        $platformId,
        $conditionData
    )
    {
        $listType = 'default';
        $boardGameGameQuery = BoardGameGameList::query()->where('board_game_id', $conditionData['boardGame']->id);

        // Фильтрация по платформе если она есть
        if ($platformId) {
            $boardGameGameQuery->where('gaming_platform_id', $platformId);
        }

        // Рулетка рерольнутых игр
        if ($conditionData['player']->rerolled_own_game_count >= 2) {
            $rerolledGames = PlayerGame::query()
                ->findByBoardGame($conditionData['boardGame']->id)
                ->where('status', PlayerGame::REROLLED)
                ->select('board_game_game_list_id')
                ->get()
                ->unique('board_game_game_list_id');

            $rerolledIds = [];

            foreach ($rerolledGames as $game) {
                $rerolledIds[] = $game['board_game_game_list_id'];
            }

            $boardGameGameQuery->whereIn('id', $rerolledIds);
            $boardGameGameQuery->where('list_type', null);

            $listType = 'rerolled';
        }

        if ($listType !== 'rerolled') {
            // Рулетка "Золотая коллекция"
            $currentPlayerGames = PlayerGame::query()
                ->findByBoardGame($conditionData['boardGame']->id)
                ->findByUserId($conditionData['user']->id)
                ->orderBy('id', 'desc')
                ->get();

            $goldenList = false;
            $rerolledGameInaRow = 0;

            foreach ($currentPlayerGames as $game) {
                if ($game->status === PlayerGame::REROLLED) {
                    $rerolledGameInaRow++;

                    if ($rerolledGameInaRow === 3) {
                        $goldenList = true;
                        break;
                    }
                } else {
                    break;
                }
            }

            if ($goldenList) {
                $boardGameGameQuery->where('list_type', BoardGameGameList::GOLDEN_LIST);

                $listType = 'golden';
            }
        }

        if ($listType === 'default') {
            $boardGameGameQuery->where('list_type', null);
        }

        $boardGameGameList = $boardGameGameQuery
            ->with([
                'game',
                'game.titleImage',
            ])
            ->active()
            ->get();

        // Убираем из списка игры, выпадали игроку
        $playerGameListQuery = PlayerGame::query()
            ->where('board_game_id', $conditionData['boardGame']->id)
            ->where('user_id', $conditionData['user']->id);

        // Если это список рерольнутых, то добавляем рерольнутые игроком игры
        if ($listType === 'rerolled') {
            $playerGameListQuery->where('status', '!=', PlayerGame::REROLLED);
        }

        $playerGameList = $playerGameListQuery->get();

        $usedGames = [];

        foreach ($playerGameList as $game) {
            $usedGames[] = $game->board_game_game_list_id;
        }

        $gameList = $boardGameGameList->filter(function ($value) use ($usedGames) {
            return !in_array($value->id, $usedGames);
        });

        // TODO возможно стоит что-то придумать когда игр 0
        return [
            'gameList' => $gameList,
            'listType' => $listType,
        ];
    }

    public function getSpendTime(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        // Проверяем, что игрок может крутить рулетку игр
        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $playerGame = PlayerGame::query()
            ->where('user_id', $conditionData['user']->id)
            ->where('board_game_id', $conditionData['boardGame']->id)
            ->where('status', PlayerGame::CURRENT)
            ->first();

        return TimerService::timeInGame($playerGame);
    }

    public function getAvailablePlayerGameList($slug, Request $request)
    {
        if (!$slug) {
            return ErrorService::message('Не получен slug');
        }

        if (!$request->name) {
            return ErrorService::message('Не получено имя пользователя');
        }

        $boardGameId = BoardGame::findBySlug($slug)->value('id');

        if (!$boardGameId) {
            return ErrorService::message('Ивент не найден');
        }

        $user = User::findByName($request->name)->value('id');

        if (!$user) {
            return ErrorService::message('Пользователь не найден');
        }

        return PlayerGameService::getAvailablePlayerGameList($boardGameId, $user);
    }
}
