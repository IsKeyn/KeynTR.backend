<?php

namespace App\Services;

use App\Http\Resources\Admin\Genre\DetailResource;
use App\Models\Genre;
use App\Services\Cache\GenreCacheService;
use Illuminate\Support\Facades\Cache;

class GenreService
{
    public function add($fields)
    {
        if (!$fields['slug']) {
            return ErrorService::message('Slug не найден');
        }

        // Проверяем slug
        $gameBySlug = Genre::findBySlug($fields['slug'])->first();

        if ($gameBySlug) {
            return ErrorService::message('Жанр с таким Slug уже существует');
        }

        if ($genre = Genre::create($fields)) {
            return $genre;
        }
    }

    public static function set($entity, $genres)
    {
        $arGenresIds = [];

        foreach ($genres as $genre) {
            if (isset($genre['genre'])) {
                $genreEntity = Genre::query()->where('id', $genre['genre'])->first();

                if ($genreEntity) {
                    $arGenresIds[] = $genreEntity->id;
                }
            }
        }

        return $entity->genres()->sync($arGenresIds);
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        $cacheKey = GenreCacheService::ADMIN_DETAIL_PREFIX . '_' . $id;

        // Выносим логику в замыкание, чтобы избежать дублирования
        $fetchData = function () use ($id, $withTrashed) {
            $item = Genre::findById($id)
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

        return Cache::remember($cacheKey, GenreCacheService::TIME, $fetchData);
    }
}
