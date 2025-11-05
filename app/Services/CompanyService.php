<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Group;

class CompanyService
{
    public static function set($entity, $data)
    {
        $arItemsIds = [];

        foreach ($data as $item) {
            if (isset($item['company'])) {
                $companyEntity = Company::query()->where('id', $item['company'])->first();

                if ($companyEntity) {
                    // Формируем данные для pivot
                    $pivotData = [];

                    // Добавляем company_role если указан
                    if (isset($item['company_role'])) {
                        $groupEntity = Group::query()->where('id', $item['company_role'])->first();

                        if ($groupEntity) {
                            $companyEntity->group($entity->id, get_class($entity))->syncWithPivotValues($groupEntity->id,
                                ['first_b_id' => $entity->id, 'first_b_type' => get_class($entity)]);
                        }
                    }

                    // Добавляем additional_info если указан
                    if (isset($item['additional_info'])) {
                        $pivotData['additional_info'] = $item['additional_info'];
                    }

                    // Если есть дополнительные данные для pivot, сохраняем с ними
                    if (!empty($pivotData)) {
                        $arItemsIds[$companyEntity->id] = $pivotData;
                    } else {
                        $arItemsIds[] = $companyEntity->id;
                    }
                }
            }
        }

        return $entity->company()->sync($arItemsIds);
    }
}
