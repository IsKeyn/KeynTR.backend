<?php

namespace App\Services;

use App\Models\Date;
use App\Models\Game;
use App\Models\GamingPlatform;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use phpDocumentor\Reflection\Types\Boolean;

class GameService extends ServiceProvider
{
    public static function setReleaseDates($entity, $releaseDates)
    {
        if (isset($releaseDates) && is_array($releaseDates)) {
            $arDatesIds = [];
            $arGamingPlatformsIds = [];

            foreach ($releaseDates as $item) {
                // Ищем дату, которая равна переданой и привязана к текущеё сущности
                $dateQuery = Date::where('date', $item['date']);
                $dateQuery->whereHas('games', function ($q) use ($entity) {
                    $q->where('games.id', $entity->id);
                });

                $gamePlatformEntity = GamingPlatform::where('id', $item['gaming_platform'])->first();

                if ($gamePlatformEntity && $gamePlatformName = $gamePlatformEntity->name) {
                    $dateQuery->whereHas('gamePlatform', function ($q) use ($gamePlatformEntity) {
                        $q->where('gaming_platforms.id', $gamePlatformEntity->id);
                    });

                    $dateEntity = $dateQuery->first();
                } else {
                    return false;
                }

                if (!$dateEntity) {
                    $dateEntity = Date::create(['date' => $item['date']]);
                }

                $arDatesIds[$dateEntity->id] = [];

                if ($gamePlatformEntity) {
                    if ($dateEntity) {
                        $dateEntity->gamePlatform()->syncWithPivotValues($gamePlatformEntity->id, ['additional_info' => $item['addInfo']]);
                        $arGamingPlatformsIds[$gamePlatformEntity->id] = [];
                    }
                }
            }

            $entity->dates()->sync($arDatesIds);
            $entity->gamePlatform()->sync($arGamingPlatformsIds);
        }
    }

    public static function setAnonsDates($entity, $anonsDates)
    {
        if (isset($anonsDates) && is_array($anonsDates)) {
            $arDatesIds = [];

            foreach ($anonsDates as $item) {
                // Ищем дату, которая равна переданой и привязана к текущеё сущности
                $dateQuery = Date::where('date', $item['date']);
                $dateQuery->whereHas('gamesAnons', function ($q) use ($entity) {
                    $q->where('games.id', $entity->id);
                });

                $dateEntity = $dateQuery->first();

                if (!$dateEntity) {
                    $dateEntity = Date::create(['date' => $item['date']]);
                }

                $arDatesIds[] = $dateEntity->id;
            }

            // TODO не работает выяснить причину
            $entity->anonsDates()->syncWithPivotValues($arDatesIds, ['type' => Game::DATE_ANONS_TYPE]);
        }
    }
}
