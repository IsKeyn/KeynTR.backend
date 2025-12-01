<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\PlayerStatusEffect;

class StatusEffectService
{
    public $statusEffect = null;
    public $conditionData = [];

    public function useStatusEffect($request)
    {
        $this->conditionData = PlayerGameService::checkConditions($request->slug);

        $result = null;

        if (isset($this->conditionData['status']) && $this->conditionData['status'] === 'error') {
            return $this->conditionData;
        } else {
            if ($request->id) {
                $usedStatusEffect = PlayerStatusEffect::query()
                    ->where('id', $request->id)
                    ->where('user_id', $this->conditionData['user']->id)
                    ->where('board_game_id', $this->conditionData['boardGame']->id)
                    ->where('active', true)
                    ->first();

                if ($usedStatusEffect) {
                    /* Получаем сущность самого статус эффекта с его данными */
                    $this->statusEffect = $usedStatusEffect->statusEffect;

                    $actionService = new ActionsService($this->conditionData, 'statusEffect', $this->statusEffect);

                    if ($this->statusEffect->actions) {
                        foreach (json_decode($this->statusEffect->actions) as $action) {
                            if ($action->type === "choice") {
                                if ($action->actions) {
                                    if ($request->type === 'accept' && isset($action->actions[1])) {
                                        $result = $actionService->activateAction($request, $action->actions[1]);
                                    }

                                    if ($request->type === 'denied' && isset($action->actions[0])) {
                                        $result = $actionService->activateAction($request, $action->actions[0]);
                                    }
                                }
                            }
                        }
                    }

                    if ($result['error'] ?? null) {
                        return $result;
                    }

                    $usedSeFields = ['active' => false];

                    if ($result && is_string($result)) {
                        $logMessage = $result;
                    } else if (isset($request->additionalParams['logMessage'])) {
                        $logMessage = $request->additionalParams['logMessage'];
                    } else {
                        if ($request->type === 'accept') {
                            $logMessage = 'принял действие статус эффекта ' . $usedStatusEffect->statusEffect->name;
                        }

                        if ($request->type === 'denied') {
                            $logMessage = 'отказался от статус эффекта ' . $usedStatusEffect->statusEffect->name;
                        }
                    }

                    LogService::addLog(
                        $this->conditionData['user']->id,
                        $this->conditionData['boardGame']->id,
                        $logMessage
                    );

                    if ($usedStatusEffect->update($usedSeFields)) {
                        if ($result && is_string($result)) {
                            return [
                                'message' => $result,
                            ];
                        } else {
                            return true;
                        }
                    }
                } else {
                    return $this->error('Статус эффект не наложен или он более не активен');
                }
            } else {
                return $this->error('Не получен ID статус эффекта игрока');
            }
        }
    }


    private function error($message)
    {
        /* Функция возврата ошибок действий с предметами */
        return ['error' => $message];
//        return response()->json(['error' => $message])->setStatusCode(Response::HTTP_OK);
    }
}
