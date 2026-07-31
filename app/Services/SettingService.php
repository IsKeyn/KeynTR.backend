<?php

namespace App\Services;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\Cache\SettingCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Setting',
            'App\Services\Cache\SettingCacheService',
            'App\Http\Resources\Admin\Setting\DetailResource',
            $id,
            ['tags', 'additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function getList($siteId = 1, $entityType = null, $entityId = null)
    {
        $cacheKey = SettingCacheService::LIST_PREFIX . '_' . $siteId;

        if ($entityType) $cacheKey .= '_' . $entityType;
        if ($entityId) $cacheKey .= '_' . $entityId;

        $time = SettingCacheService::TIME;

        return Cache::remember($cacheKey, $time, function () use ($siteId, $entityType, $entityId) {
            $settings = Setting::query()
                ->where('site_id', $siteId)
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->active();

            if (!isset($request->sort)) {
                $settings->orderByRaw('sort IS NULL, sort ASC');
            }

            $result = $settings->get();

            return SettingResource::collection($result);
        });
    }

    public static function set($entity, $data, $key = 'settings')
    {
        $arIds = [];

        foreach ($data as $element) {
            if (isset($element[$key])) {
                $elementEntity = Setting::query()->where('id', $element[$key])->first();

                if ($elementEntity) {
                    $arIds[] = $elementEntity->id;
                }
            }
        }

        return $entity->series()->syncWithPivotValues($arIds, []);
    }

    public function sync(Model $entity, array $fields = []): void
    {
        DB::transaction(function () use ($entity, $fields) {
            $incomingIds = [];
            $newFields = [];
            $fieldsToUpdate = [];

            // 1. Подготавливаем данные
            foreach ($fields as $field) {
                if (!empty($field['id'])) {
                    $incomingIds[] = $field['id'];
                    $fieldsToUpdate[$field['id']] = $field;
                } elseif (!empty($field['name'])) {
                    // Убираем id, если он вдруг пришел как null или пустая строка
                    $newFields[] = Arr::except($field, ['id']);
                }
            }

            // 2. Загружаем существующие настройки один раз и индексируем по id
            $existingSettings = $entity->settings()->get()->keyBy('id');

            // 3. Обновляем и удаляем
            foreach ($existingSettings as $id => $setting) {
                if (in_array($id, $incomingIds, true)) {
                    $data = $fieldsToUpdate[$id];
                    unset($data['id']); // Исключаем id из массива для update

                    // Обновляем только если данные реально изменились (оптимизация)
                    if ($setting->isDirty() || array_diff_assoc($data, $setting->only(array_keys($data))) !== []) {
                        $setting->update($data);
                    }
                } else {
                    // Удаляем. Используем delete() на модели, чтобы сработали Eloquent-события (deleting/deleted)
                    $setting->delete();
                }
            }

            // 4. Массовое создание (один INSERT запрос вместо N)
            if (!empty($newFields)) {
                $entity->settings()->createMany($newFields);
            }
        });
    }
}
