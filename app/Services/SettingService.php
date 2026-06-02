<?php

namespace App\Services;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\Cache\SettingCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\Cache;

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
}
