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
                    $arItemsIds[] = $companyEntity->id;

                    if (isset($item['company_role'])) {
                        $groupEntity = Group::query()->where('id', $item['company_role'])->first(); // Проверяем, что группа существует

                        if ($groupEntity) {
                            $companyEntity->group($entity->id, get_class($entity))->syncWithPivotValues($groupEntity->id,
                                ['first_b_id' => $entity->id, 'first_b_type' => get_class($entity)]);
                        }
                    }
                }
            }
        }

        return $entity->company()->sync($arItemsIds);
    }
}
