<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Services\Entity\EntityService;
use Illuminate\Http\Request;

class StatusEffectService
{
    public $statusEffect = null;
    public $conditionData = [];

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            StatusEffect::class,
            StatusEffect::CACHE_SERVICE,
            StatusEffect::DETAIL_RESOURCE,
            $id,
            [
                'tags',
                'additionalFields',
                'titleImage',
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
     * Функция ручного применения статус эффекта
     *
     * @param Request $request  Данные запроса
     * @return array Тип возвращаемых данных
     */
    public function useStatusEffect($request): array
    {
        $this->conditionData = PlayerGameService::checkConditions($request->slug);

        $result = null;

        if (isset($this->conditionData['status']) && $this->conditionData['status'] === 'error') {
            return $this->conditionData;
        }

        if (!$request->id) {
            return $this->error(__('boardGame.player.status_effect.dont_received_se_id'));
        }

        $usedStatusEffect = PlayerStatusEffect::query()
            ->where('id', $request->id)
            ->findByPlayer($this->conditionData['player']->id)
            ->active()
            ->with([
                'statusEffectBind.statusEffect'
            ])
            ->first();

        if (!$usedStatusEffect) {
            return $this->error(__('boardGame.player.status_effect.dont_dont_have_or_inactive'));
        }

        $statusEffect = $usedStatusEffect->statusEffectBind->statusEffect;

        if (!$statusEffect) {
            return $this->error(__('boardGame.player.status_effect.not_found_or_inactive'));
        }

        $actionService = new ActionsService(
            $this->conditionData,
            'statusEffect',
            $statusEffect
        );

        if ($statusEffect->actions) {
            foreach (json_decode($statusEffect->actions) as $action) {
                if (!$action->type || !$action->actions) continue;

                if ($action->type === "choice") {
                    if ($request->type === 'accept' && isset($action->actions[1])) {
                        $result = $actionService->activateAction($request, $action->actions[1]);
                    }

                    if ($request->type === 'denied' && isset($action->actions[0])) {
                        $result = $actionService->activateAction($request, $action->actions[0]);
                    }
                }

                if ($action->type === "onlyAccept") {
                    foreach ($action->actions as $actionForActivate) {
                        $result = $actionService->activateAction($request, $actionForActivate);
                    }
                }
            }
        }

        if ($result['error'] ?? null) return $result;

        $usedSeFields = ['active' => false];

        if ($result && is_string($result)) {
            $logMessage = $result;
        } else if (isset($request->additionalParams['logMessage'])) {
            $logMessage = $request->additionalParams['logMessage'];
        } else {
            if ($request->type === 'accept') {
                $logMessage = __('boardGame.player.status_effect.accepted', [
                    'name' => $statusEffect->name
                ]);
            }

            if ($request->type === 'denied') {
                $logMessage = __('boardGame.player.status_effect.denied', [
                    'name' => $statusEffect->name
                ]);
            }
        }

        LogService::addLog(
            $this->conditionData['user']->id,
            $this->conditionData['boardGame']->id,
            $logMessage,
            $this->conditionData['player']->id,
        );

        if ($usedStatusEffect->update($usedSeFields)) {
            if ($result && is_string($result)) {
                return [
                    'message' => $result,
                ];
            } else {
                return [
                    'message' => __('boardGame.player.status_effect.successfully_applied'),
                ];
            }
        }
    }

    /**
     * Функция возврата ошибок действий с предметами
     *
     * @param string $message Сообщение ошибки
     * @return array Сформированный массив ошибки
     */
    private function error($message): array
    {
        return ['error' => $message];
    }
}
