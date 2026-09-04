<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\StatusEffects\BgPlayerStatusEffectBindResource;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\BoardGame\StatusEffectBind;
use App\Services\Cache\BoardGame\StatusEffect\BgStatusEffectBindCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

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
     * Список привязанных к ивенту статус эффекты
     *
     * @param int $bgId
     * @return mixed
     */
    public static function statusEffectsInBoardGame(int $bgId)
    {
        $cacheKey = BgStatusEffectBindCacheService::LIST_PREFIX . '_' . $bgId;

        return Cache::remember($cacheKey, BgStatusEffectBindCacheService::TIME, function () use ($bgId) {
            $items = StatusEffectBind::query()
                ->active()
                ->findByBoardGame($bgId)
                ->with([
                    'statusEffect',
                    'statusEffect.titleImage',
                ])
                ->get()
                ->values();

            return BgPlayerStatusEffectBindResource::collection($items);
        });
    }

    /**
     * Функция накладывает статус эффект по привязке к ивенту $statusEffectBindId на игрока из $conditionData
     *
     * @param array $conditionData
     * @param int $statusEffectBindId
     */
    public function setStatusEffect(
        array $conditionData,
        int $statusEffectBindId
    )
    {
        if (!$statusEffectBindId) {
            abort(Response::HTTP_BAD_REQUEST, __('boardGame.status_effect.dont_received_se_bind_id'));
        }

        $statusEffectBind = StatusEffectBind::query()
            ->with('statusEffect:id,slug')
            ->find($statusEffectBindId);

        if (!$statusEffectBind) {
            abort(Response::HTTP_BAD_REQUEST, __('boardGame.status_effect.dont_dont_have_or_inactive'));
        }

        $slug = $statusEffectBind?->statusEffect?->slug;

        if (!$slug) {
            abort(Response::HTTP_BAD_REQUEST, __('boardGame.status_effect.dont_received_slug'));
        }

        $actionService = new ActionsService($conditionData, 'statusEffect', $statusEffectBind->statusEffect);
        return $actionService->activateAction(
            (Object) [],
            (Object) [
                'type' => 'applyStatusEffect',
                'target' => 'current',
                'value' => $slug
            ]
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
            foreach ($statusEffect->actions as $action) {
                $action = (Object) $action;

                if (!$action->type || !$action->actions) continue;

                if ($action->type === "choice") {
                    if ($request->type === 'accept' && isset($action->actions[1])) {
                        $result = $actionService->activateAction($request, (Object) $action->actions[1]);
                    }

                    if ($request->type === 'denied' && isset($action->actions[0])) {
                        $result = $actionService->activateAction($request, (Object) $action->actions[0]);
                    }
                }

                if ($action->type === "onlyAccept") {
                    foreach ($action->actions as $actionForActivate) {
                        $result = $actionService->activateAction($request, (Object) $actionForActivate);
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

    public static function activateAdditionalAction(
        $conditionData,
        $playerStatusEffects,
        $type,
        $additionActionOn,
        $freeReroll = false
    )
    {
        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === $type) {
                foreach ($statusEffect->statusEffectBind->statusEffect->actions as $action) {
                    $action = (Object) $action;
                    $actionService = new ActionsService($conditionData, 'statusEffect', $statusEffect->statusEffectBind->statusEffect);

                    if (
                        isset($action->autoActions)
                    ) {
                        foreach ($action->autoActions as $autoAction) {
                            if (
                                isset($autoAction['type'])
                                && $autoAction['type'] === $additionActionOn
                                && isset($autoAction['actions'])
                            ) {
                                $shouldSkip = $additionActionOn === 'reroll' && $freeReroll && $autoAction['disableOnFreeReroll'];

                                if (!$shouldSkip) {
                                    foreach ($autoAction['actions'] as $action) {
                                        $actionService->activateAction(
                                            (object)[],
                                            (object)$action
                                        );
                                    }
                                }

                                $statusEffect->update(['active' => false]);
                            }
                        }
                    }
                }
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
