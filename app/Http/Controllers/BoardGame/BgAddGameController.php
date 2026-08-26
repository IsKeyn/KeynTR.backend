<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\AddGame\AddGameResource;
use App\Models\BoardGame\AddGame;
use App\Services\BoardGame\BgAddGameService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\Cache\BoardGame\BgAddGameCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BgAddGameController extends Controller
{
    public function check(
        Request $request,
        $slug
    )
    {
        if (!$slug) {
            return response()
                ->json(['error' => __('boardGame.not_received_slug')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $bgAddGameService = app(BgAddGameService::class);

        $checkResult = $bgAddGameService->checkCanPlayerAddGame($conditionData);

        $existingRecords = null;

        if ($checkResult['status'] === AddGame::STATUS_CAN_ADD) {
            $existingRecords = AddGame::query()->where('bg_player_id', $conditionData['player']->id)->get();
        }

        return response()
            ->json([
                'result' => $checkResult,
                'records' => $existingRecords ? AddGameResource::collection($existingRecords) : null,
            ])
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Сохранение черновика добавленных игр
     *
     * @param Request $request
     * @param String $slug
     *
     * @return array|\Illuminate\Http\JsonResponse|mixed|string[]
     */
    public function save(
        Request $request,
        String $slug
    )
    {
        if (!$slug) {
            return response()
                ->json(['error' => __('boardGame.not_received_slug')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $request->validate([
            'data' => 'required|array', // Указываем, что data должен быть массивом
            'data.*.name' => 'nullable|string|max:1000',
            'data.*.gaming_platform_id' => 'nullable|integer|exists:gaming_platforms,id',
            'data.*.coop' => 'nullable|boolean',
            'data.*.game_completion_time' => 'nullable|string|max:1000',
            'data.*.difficulty' => 'nullable|integer|min:0|max:100',
            'data.*.description' => 'nullable|string|max:5000',
            'data.*.comment_for_moderator' => 'nullable|string|max:5000',
            'data.*.moderator_comment' => 'nullable|string|max:5000',
            'data.*.status' => 'nullable|integer',
            'data.*.sort' => 'nullable|integer',
            'data.*.active' => 'nullable|boolean',
        ], [
            // Общие ошибки для всего массива
            'data.required' => 'Необходимо передать массив данных.',
            'data.array' => 'Поле данных должно быть массивом.',

            // Ошибки для конкретных полей и правил (используем *)
            'data.*.name.max' => 'Название не должно превышать :max символов.',

            'data.*.gaming_platform_id.integer' => 'ID платформы должен быть целым числом.',
            'data.*.gaming_platform_id.exists' => 'Выбранная игровая платформа не существует.',

            'data.*.difficulty.min' => 'Сложность не может быть меньше :min.',
            'data.*.difficulty.max' => 'Сложность не может быть больше :max.',

            'data.*.description.max' => 'Описание не должно превышать :max символов.',

            'data.*.active.boolean' => 'Значение активности должно быть true или false.',
        ]);

        $bgAddGameService = app(BgAddGameService::class);

        $checkResult = $bgAddGameService->checkCanPlayerAddGame($conditionData);

        if ($checkResult['status'] !== AddGame::STATUS_CAN_ADD) {
            return response()
                ->json(['error' => __('boardGame.add_game.cant_add')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return DB::transaction(function () use ($conditionData, $request) {
            $existingRecords = AddGame::query()->where('bg_player_id', $conditionData['player']->id)->get();

            // Массивы для операций
            $toUpsert = [];
            $toCreate = [];
            $incomingIds = [];

            foreach ($request->data as $item) {
                $item['bg_player_id'] = $conditionData['player']->id;
                $item['user_id'] = $conditionData['player']->user_id;
                $item['board_game_id'] = $conditionData['boardGame']->id;

                if (isset($item['id'])) {
                    // Не обновляем поля, при определенных статусах
                    if ($item['status'] !== AddGame::ADD_STATUS_UNDER_CONSIDERATION
                        && $item['status'] !== AddGame::ADD_STATUS_ADDED
                    ) {
                        if ($request->submitDataForReview) {
                            $item['status'] = AddGame::ADD_STATUS_UNDER_CONSIDERATION;
                        } else {
                            $item['status'] = AddGame::ADD_STATUS_DRAFT;
                        }

                        $toUpsert[] = $item;
                    }

                    $incomingIds[] = $item['id'];
                } else {
                    $item['status'] = AddGame::ADD_STATUS_DRAFT;
                    $toCreate[] = $item;
                }
            }

            $bgAddGameCacheService = app(BgAddGameCacheService::class);


            if (!empty($toUpsert)) {
                foreach ($toUpsert as $item) {
                    if (isset($item['id'])) {
                        $bgAddGameCacheService->clearAdminDetailCacheById($item['id']);
                    }
                }

                AddGame::upsert(
                    $toUpsert,
                    ['id', 'bg_player_id']
                );
            }

            if (!empty($toCreate)) {
                AddGame::insert($toCreate);
            }

            $toDelete = $existingRecords->filter(function ($record) use ($incomingIds) {
                return !in_array($record->id, $incomingIds);
            });

            if ($toDelete->isNotEmpty()) {
                foreach ($toDelete as $item) {
                    if (isset($item['id'])) {
                        $bgAddGameCacheService->clearAdminDetailCacheById($item['id']);
                    }
                }

                AddGame::whereIn('id', $toDelete->pluck('id'))->delete();
            }

            $bgAddGameCacheService->clearListCache();

            return true;
        });
    }
}
