<?php

namespace App\Services;

use App\Http\Resources\Admin\Company\ListResource;
use App\Models\Company;
use App\Models\Group;
use App\Services\Cache\CompanyCacheService;
use Illuminate\Support\Facades\Cache;

class CompanyService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $gameBySlug = Company::findBySlug($fields['slug'])->first();

        if ($gameBySlug) {
            return ErrorService::message('Компания с таким Slug уже существует');
        }

        if ($company = Company::create($fields)) {
            return $company;
        }
    }

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

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        $cacheKey = CompanyCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed) {
            $item = Company::findById($id)
                ->with([
                    'titleImage',
                    'cover',
                    'tags',
                    'additionalFields',
                    'seo',
                    'seo.entity',
                    'seo.entity.tags',
                ]);

            if ($withTrashed) {
                $item->withTrashed();
            }

            $item = $item->first();

            return ListResource::make($item);
        };

        // Если передан флаг принудительного обновления, игнорируем кеш
        if ($forceRefresh) {
            return $fetchData();
        }

        return Cache::remember($cacheKey, CompanyCacheService::TIME, $fetchData);
    }
}
