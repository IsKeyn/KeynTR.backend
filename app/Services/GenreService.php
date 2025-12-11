<?php

namespace App\Services;

use App\Models\Genre;

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
}
