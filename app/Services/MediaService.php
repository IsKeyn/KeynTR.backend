<?php

namespace App\Services;

use App\Models\Media;
use phpDocumentor\Reflection\Types\Boolean;

class MediaService
{
    public function setTitleImage($entity, $mediaId)
    {
        $media = Media::query()->where('id', $mediaId)->first();

        if ($media) {
            return $entity->titleImage()->syncWithPivotValues($media->id, ['type' => Media::TITLE_TYPE]);
        }
    }

    public function setCovers($entity, $coversArray)
    {
        $arCoversIds = [];

        foreach ($coversArray as $cover) {
            if (isset($cover['id'])) {
                $media = Media::query()->where('id', $cover['id'])->first();


                $arCoversIds[] = $media->id;
            }
        }

        return $entity->cover()->syncWithPivotValues($arCoversIds, ['type' => Media::COVER_TYPE]);
    }
}
