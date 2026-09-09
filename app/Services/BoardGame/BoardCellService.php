<?php

namespace App\Services\BoardGame;

use App\Http\Requests\BoardGame\BoardCellReviewSetRequest;
use App\Models\BoardGame\BoardCellReview;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\User;
use App\Services\Cache\BoardGame\BoardCellReviewCacheService;
use App\Services\CommentService;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BoardCellService
{
    /**
     * Получение элемента по его ID
     *
     * @param int $id ID элемента
     * @param bool $forceRefresh принудительная очистка кеша
     * @param bool $withTrashed с удаленными
     * @return mixed
     */
    public static function getById(
        int $id,
        bool $forceRefresh = false,
        bool $withTrashed = false
    )
    {
        return EntityService::getById(
            BoardCellReview::class,
            BoardCellReview::CACHE_SERVICE,
            BoardCellReview::DETAIL_RESOURCE,
            $id,
            [
                'comment',
                'comment.user',
                'user',
                'user.avatar',
                'boardGame',
                'settings',
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

    /**
     * @param BoardGamePlayer $player
     * @param BoardGame $boardGame
     * @param int $boardPositionEffectsId
     */
    public function getCurrentPlayerReview(
        BoardGamePlayer $player,
        BoardGame $boardGame,
        int $boardPositionEffectsId
    )
    {
        $result = BoardCellReview::query()
            ->where('player_id', $player->id)
            ->where('board_game_id', $boardGame->id)
            ->where('board_position_effects_id', $boardPositionEffectsId)
            ->with([
                'comment',
                'comment.user',
                'user',
                'user.avatar',
                'boardGame',
            ])->first();

        return $result;
    }

    /**
     * @param User $user
     * @param BoardGamePlayer $player
     * @param BoardGame $boardGame
     * @param object $data
     * @return BoardCellReview
     */
    public function setReview(
        User $user,
        BoardGamePlayer $player,
        BoardGame $boardGame,
        array $data,
        BoardCellReviewSetRequest $request
    ): BoardCellReview
    {
        return DB::transaction(function () use ($user, $player, $boardGame, $data, $request) {
            $comment = CommentService::addComment(
                $request,
                [
                    'message' => $data['comment'],
                    'entity_type' => $data['entity_type'],
                    'entity_id' => $data['entity_id'],
                ]
            );

            $newReview = BoardCellReview::create(
                [
                    'player_id' => $player->id,
                    'user_id' => $user->id,
                    'board_game_id' => $boardGame->id,
                    'board_position_effects_id' => $data['entity_id'],
                    'completion_time_seconds' => $data['completion_time_seconds'],
                    'comment_id' => $comment->original->id,
                ]
            );

            return $newReview;
        });
    }

    public function getCurrentEventReview(
        $bgId,
        $cellEffectId,
        $page = 1,
        $perPage = 10,
        $fullList = false
    ) {
        $cacheKey = BoardCellReviewCacheService::LIST_PREFIX . '_' . $bgId . '_' . $cellEffectId;

        if (!$fullList) {
            $cacheKey .= '_' . $page . '_' . $perPage;
        }

        $cacheKey .= '_in_event';

        return Cache::remember($cacheKey, BoardCellReviewCacheService::TIME, function () use (
            $bgId,
            $cellEffectId,
            $fullList,
            $perPage,
            $page
        ) {
            $cellReviews = BoardCellReview::query()
                ->where('board_game_id', $bgId)
                ->where('board_position_effects_id', $cellEffectId)
                ->with([
                    'comment',
                    'comment.user',
                    'user',
                    'user.avatar',
                    'boardGame',
                    'boardGame.media',
                ]);

            if ($fullList) {
                $result = $cellReviews->get();
            } else {
                // Встроенная пагинация сама создаст LengthAwarePaginator
                $result = $cellReviews->paginate($perPage, ['*'], 'page', $page);
            }

            return $result;
        });
    }

    public function getOtherEventReview(
        $bgId,
        $cellEffectId,
        $page = 1,
        $perPage = 10,
        $fullList = false
    ) {
        $cacheKey = BoardCellReviewCacheService::LIST_PREFIX . '_' . $bgId . '_' . $cellEffectId;

        if (!$fullList) {
            $cacheKey .= '_' . $page . '_' . $perPage;
        }

        $cacheKey .= '_in_other_events';

        return Cache::remember($cacheKey, BoardCellReviewCacheService::TIME, function () use (
            $bgId,
            $cellEffectId,
            $fullList,
            $perPage,
            $page
        ) {
            $cellReviews = BoardCellReview::query()
                ->whereIn('board_game_id', function ($query) use ($bgId) {
                    $query->select('id')
                        ->from('board_games')
                        ->where('is_test', '!=', true)
                        ->where('id', '!=', $bgId);
                })
                ->where('board_position_effects_id', $cellEffectId)
                ->with([
                    'comment',
                    'comment.user',
                    'user',
                    'user.avatar',
                    'boardGame',
                    'boardGame.media',
                ]);

            if ($fullList) {
                $result = $cellReviews->get();
            } else {
                // Встроенная пагинация сама создаст LengthAwarePaginator
                $result = $cellReviews->paginate($perPage, ['*'], 'page', $page);
            }

            return $result;
        });
    }

}
