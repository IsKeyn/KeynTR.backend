<?php

namespace App\Services;

use App\Http\Resources\GamingPlatform\DetailResource;
use App\Models\Date;
use App\Models\GamingPlatform;
use App\Services\Cache\GamingPlatformCacheService;
use Illuminate\Support\Facades\Cache;
use phpDocumentor\Reflection\Types\Boolean;

class GamingPlatformService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $gameBySlug = GamingPlatform::findBySlug($fields['slug'])->first();

        if ($gameBySlug) {
            return ErrorService::message('Игровая платформа с Slug: ' . $fields['slug'] . ' уже существует');
        }

        if ($gamingPlatform = GamingPlatform::create($fields)) {
            $this->setAdditionalFields($gamingPlatform, $fields);
            return $gamingPlatform;
        }
    }

    public function setAdditionalFields($model, $validated) {
        if (isset($validated['release_dates'])) {
            $this->setReleaseDates($model, $validated['release_dates']);
        }
    }

    public static function setReleaseDates($entity, $anonsDates)
    {
        if (isset($anonsDates) && is_array($anonsDates)) {
            $arDatesIds = [];

            foreach ($anonsDates as $item) {
                if (isset($item['date']) && $item['date']) {
                    /* Ищем дату, которая равна переданой и привязана к текущеё сущности с типом DATE_ANONS_TYPE */
                    $dateQuery = Date::where('date', $item['date']);
                    $dateQuery->whereHas(
                        'gamingPlatforms',
                        function ($q) use ($entity) {
                            $q->where('gaming_platforms.id', $entity->id);
                        }
                    );

                    $dateEntity = $dateQuery->first();

                    if (!$dateEntity) {
                        $dateEntity = Date::create(['date' => $item['date']]);
                    }

                    $arDatesIds[] = $dateEntity->id;
                }
            }

            // TODO не работает выяснить причину
            $entity->realiseDates()->syncWithPivotValues($arDatesIds, ['type' => GamingPlatform::REALISE_TYPE]);
        }
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        $cacheKey = GamingPlatformCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed) {
            $item = GamingPlatform::findById($id)
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

            return DetailResource::make($item);
        };

        // Если передан флаг принудительного обновления, игнорируем кеш
        if ($forceRefresh) {
            return $fetchData();
        }

        return Cache::remember($cacheKey, GamingPlatformCacheService::TIME, $fetchData);
    }
}
