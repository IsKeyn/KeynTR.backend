<?php

namespace App\Services;

use App\Models\Genre;

class GenreService
{
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
