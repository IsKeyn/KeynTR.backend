<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\PlayerInteractions;
use App\Services\ErrorService;
use App\Services\NotificationService;

class InteractionsService
{
    private $interaction;
    private $conditionData = [];

    public function action($request)
    {
        if (!$request->slug) return ErrorService::message('Не получен SLUG');
        if (!$request->id) return ErrorService::message('Не получен ID взаимодействия');
        if (!$request->type) return ErrorService::message('Не получен тип взаимодействия');

        $this->conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($this->conditionData['status']) && $this->conditionData['status'] === 'error') {
            return $this->conditionData;
        } else {
            $this->interaction = $this->checkInteraction($request->id);

            if (isset($this->interaction['status']) && $this->interaction['status'] === 'error') {
                return $this->interaction;
            } else {
                switch ($request->type) {
                    case 'accept': return $this->accept();
                    case 'refuse': return $this->refuse();
                    case 'recall': return $this->recall();
                }
            }
        }
    }

    private function accept()
    {

    }

    private function refuse()
    {

    }

    private function recall()
    {
        if ($this->interaction->created_by !== $this->conditionData['user']->id) {
            return ErrorService::message('Вы не можете отозвать взаимодействие, которое создано не вами');
        }

        if ($this->interaction->status === PlayerInteractions::STATUS_ACTIVE) {
            if ($this->interaction->with_player) {
                $fields = [
                    'user_id' => $this->interaction->with_player,
                    'created_by' => $this->conditionData['user']->id,
                    'message' => 'Отозвал предложение "' . PlayerInteractions::TYPE_NAME['ru'][$this->interaction->type] . '"',
                ];

                NotificationService::set($fields);
            }

            if ($this->interaction->entity_id && $this->interaction->entity_type) {
                if ($this->interaction->type === 'switchGame') {
                    if ($this->interaction->entity_type === 'App\Models\BoardGame\BoardGameInventory') {
                        $inventoryItem = $this->interaction->entity_type::findById($this->interaction->entity_id)->first();

                        if ($inventoryItem->has_used) {
                            $inventoryItem->has_used = false;
                            $inventoryItem->save();
                        }
                    }
                }
            }

            $this->interaction->active = false;
            return $this->interaction->save();
        } else {
            return ErrorService::message('Вы не можете отозвать взаимодействие, которое находится в статусе отличном от "Отправлено"');
        }
    }

    private function checkInteraction($id)
    {
        $playerInteractions = PlayerInteractions::findById($id)->first();

        if (!$playerInteractions) {
            return [
                'status' => 'error',
                'status_message' => 'Взаимодействия не существует',
            ];
        }

        if (!$playerInteractions->active) {
            return [
                'status' => 'error',
                'status_message' => 'Взаимодействие не активно',
            ];
        }

        return $playerInteractions;
    }
}
