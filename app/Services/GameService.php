<?php

namespace App\Services;

use App\Http\Resources\Admin\AdminGameResource;
use App\Models\Date;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Seo;
use App\Services\Cache\GameCacheService;
use Illuminate\Support\Facades\Cache;
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
            $relatedDataService = app(RelatedDataService::class);
            $relatedDataService->set($game, $fields);

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
                    $hideDay = isset($item['hideDay']) ? $item['hideDay'] : false;
                    $hideMonth = isset($item['hideMonth']) ? $item['hideMonth'] : false;

                    $dateQuery = Date::where('date', $item['date'])->where('hide_day', $hideDay)->where('hide_month', $hideMonth);
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
                        $dateEntity = Date::create(
                            [
                                'date' => $item['date'],
                                'hide_day' => $hideDay,
                                'hide_month' => $hideMonth,
                            ]
                        );
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
                    $hideDay = isset($item['hideDay']) ? $item['hideDay'] : false;
                    $hideMonth = isset($item['hideMonth']) ? $item['hideMonth'] : false;

                    $dateQuery = Date::where('date', $item['date'])->where('hide_day', $hideDay)->where('hide_month', $hideMonth);
                    $dateQuery->whereHas(
                        'gamesAnons',
                        function ($q) use ($entity) {
                            $q->where('games.id', $entity->id);
                        }
                    );

                    $dateEntity = $dateQuery->first();

                    if (!$dateEntity) {
                        $dateEntity = Date::create(
                            [
                                'date' => $item['date'],
                                'hide_day' => $hideDay,
                                'hide_month' => $hideMonth,
                            ]
                        );
                    }

                    $arDatesIds[] = $dateEntity->id;
                }
            }

            // TODO не работает выяснить причину
            $entity->anonsDates()->syncWithPivotValues($arDatesIds, ['type' => Game::DATE_ANONS_TYPE]);
        }
    }

    public static function getGameById($id, $forceRefresh = false, $withTrashed = false)
    {
        $cacheKey = GameCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed) {
            $game = Game::findById($id)
                ->with([
                    'titleImage',
                    'cover',
                    'gamePlatform',
                    'dates',
                    'dates.gamePlatform',
                    'anonsDates',
                    'tags',
                    'series',
                    'groups',
                    'genres',
                    'company',
                    'company.group',
                    'link',
                    'additionalFields',
                    'seo',
                    'seo.entity',
                    'seo.entity.tags',
                    'menu',
                    'menu.elements',
                    'blocks',
                    'bgGamesList',
                    'bgGamesList.boardGame',
                    'bgGamesList.boardGame.titleImage',
                ]);

            if ($withTrashed) {
                $game->withTrashed();
            }

            $game = $game->first();

            return AdminGameResource::make($game);
        };

        // Если передан флаг принудительного обновления, игнорируем кеш
        if ($forceRefresh) {
            return $fetchData();
        }

        return Cache::remember($cacheKey, GameCacheService::TIME, $fetchData);
    }
}
