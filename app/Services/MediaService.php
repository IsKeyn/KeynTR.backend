<?php

namespace App\Services;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
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

    public function setMediaGroup($entity, $galleryArray)
    {
        $arGalleryIds = [];

        foreach ($galleryArray as $gallery) {
            if (isset($gallery['id'])) {
                $mediaObj = Media::query()->where('id', $gallery['id'])->first();


                $arGalleryIds[] = $mediaObj->id;
            }
        }

        return $entity->mediaGroup()->syncWithPivotValues($arGalleryIds, ['type' => Media::MEDIA_GROUP]);
    }

    public function getWebp(MediaResource $media)
    {
        $originalPath = "media/$media->id/$media->file_name";
        $webpPath = str_replace($media->mime_type, 'webp', $originalPath);

        /* Проверяем, что файл, который мы собираемся обрабатывать существуе */
        if (!Storage::disk('public')->exists($originalPath)) {
            return false;
        }

        if (!Storage::disk('public')->exists($webpPath)) {
            $image = Image::read(storage_path("app/public/$originalPath"));
            $image->encode(new WebpEncoder(80))->save(storage_path("app/public/$webpPath"));
        }

        return config('app.url') . "/storage/" . str_replace('\\', '/', $webpPath);
    }

    public function getResizes(MediaResource $media) {
        $originalPath = "media/$media->id/$media->file_name";

        /* Проверяем, что файл, который мы собираемся обрабатывать существуе */
        if (!Storage::disk('public')->exists($originalPath)) {
            return false;
        }

        $resizesList = [
            300 => [],
            500 => [],
        ];

        $returnData = [];

        foreach ($resizesList as $resizeWidth => &$resize) {
            $resize['path'] = str_replace(".$media->mime_type", '', $originalPath) . '_' . $resizeWidth . ".webp";

            if (!Storage::disk('public')->exists($resize['path'])) {
                $resize['image'] = Image::read(storage_path("app/public/$originalPath"));

                $resize['image']->scale($resizeWidth)->save(storage_path('app/public/' . $resize['path']));
            }

            $returnData['r_' . $resizeWidth] = config('app.url') . "/storage/" . str_replace('\\', '/', $resize['path']);
        }

        return $returnData;
    }
}
