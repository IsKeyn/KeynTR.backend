<?php

namespace App\Services;

use App\Models\Media;

class MutationService
{
    public static function setMedia($entity, $field)
    {
        if (!isset($field)) return false;

        $media = Media::findById($field)->first();

        if (!$media) return false;
        if (!$media->id) return false;

        return $entity->media()->syncWithPivotValues($media->id, ['type' => Media::TITLE_TYPE]);
    }

    public static function setTags($entity, $field)
    {
        if (!isset($field)) return false;
        return TagService::attacheTagsToEntity($entity, $field);
    }
}
