<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\AddGame\AddGameResource;
use App\Models\BoardGame\AddGame;
use App\Services\BoardGame\BgAddGameService;
use App\Services\BoardGame\PlayerGameService;
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

    public function save(
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

            if (!empty($toUpsert)) {
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
                AddGame::whereIn('id', $toDelete->pluck('id'))->delete();
            }

            return true;
        });
    }
}
