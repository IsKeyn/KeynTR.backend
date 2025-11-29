<?php

namespace App\Services;

use App\Models\Date;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Seo;
use phpDocumentor\Reflection\Types\Boolean;

class GameService
{
    public function addGame($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $gameBySlug = Game::findBySlug($fields['slug'])->first();

        if ($gameBySlug) {
            return ErrorService::message('Игра с таким Slug уже существует');
        }

        if ($game = Game::create($fields)) {
            $this->setAdditionalFields($game, $fields);
            return $game;
        }
    }

    public function setAdditionalFields($model, $validated) {
        $mediaService = new MediaService();

        if (isset($validated['title_image'])) {
            $mediaService->setTitleImage($model, $validated['title_image']);
        }

        if (isset($validated['covers'])) {
            $mediaService->setCovers($model, $validated['covers']);
        }

        if (isset($validated['additional_fields'])) {
            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($model, $validated['additional_fields']);
        }

        if (isset($validated['groups'])) {
            GroupService::set($model, $validated['groups']);
        }

        if (isset($validated['genres'])) {
            GenreService::set($model, $validated['genres']);
        }

        if (isset($validated['companies'])) {
            CompanyService::set($model, $validated['companies']);
        }

        if (isset($validated['tags'])) {
            TagService::attacheTagsToEntity($model, $validated['tags']);
        }

        if (isset($validated['seo']) && $validated['seo']) {
            if ($model->seo) {
                $model->seo()->update($validated['seo']);
            } else {
                $meta = new Seo($validated['seo']);
                $model->seo()->save($meta);
            }
        }

        if (isset($validated['anons_dates'])) {
            GameService::setAnonsDates($model, $validated['anons_dates']);
        }

        if (isset($validated['release_dates'])) {
            GameService::setReleaseDates($model, $validated['release_dates']);
        }

        if (isset($validated['links'])) {
            LinkService::set($model, $validated['links']);
        }

        if (isset($validated['blocks'])) {
            BlockService::set($model, $validated['blocks']);
        }
    }

    public static function setReleaseDates($entity, $releaseDates)
    {
        if (isset($releaseDates) && is_array($releaseDates)) {
            $arDatesIds = [];
            $arGamingPlatformsIds = [];

            foreach ($releaseDates as $item) {
                if (isset($item['date']) && $item['date']) {
                    /* Ищем дату, которая равна переданой и привязана к текущеё сущности */
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
                            $dateEntity->gamePlatform()->syncWithPivotValues($gamePlatformEntity->id,
                                ['additional_info' => $item['addInfo']]);
                            $arGamingPlatformsIds[$gamePlatformEntity->id] = [];
                        }
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
                if (isset($item['date']) && $item['date']) {
                    /* Ищем дату, которая равна переданой и привязана к текущеё сущности с типом DATE_ANONS_TYPE */
                    $dateQuery = Date::where('date', $item['date']);
                    $dateQuery->whereHas(
                        'gamesAnons',
                        function ($q) use ($entity) {
                            $q->where('games.id', $entity->id);
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
            $entity->anonsDates()->syncWithPivotValues($arDatesIds, ['type' => Game::DATE_ANONS_TYPE]);
        }
    }
}
