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
    public function addMedia($fileArray, $user, $name = null)
    {
        $fileData = [];

        if (isset($fileArray['name'])) $fileData['name'] = $fileArray['name'];
        if (isset($fileArray['description'])) $fileData['description'] = $fileArray['description'];
        if (isset($fileArray['type'])) $fileData['name'] = $fileArray['type'];

        $fileData['created_by'] = $user->id;

        $media = Media::create($fileData);

        if (isset($fileArray['tags'])) {
            foreach ($fileArray['tags'] as $tagName) {
                $tag = TagService::findOrCreateTag($tagName);
                $media->tags()->save($tag);
            }
        }

        $file = $fileArray['src'];

        if (!$name) {
            $name = $fileArray['src']->getClientOriginalName();
        }

        // Проверяем, есть ли уже расширение в имени
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        if (empty($ext)) {
            // Получаем расширение из MIME-типа (использует Symfony MIME Guesser)
            $guessedExt = $file->guessExtension();

            if ($guessedExt) {
                $name .= '.' . strtolower($guessedExt);
            }
        }

        $path = $fileArray['src']->storeAs(
            'media/' . $media->id,
            $name,
            'public'
        );

//        $img = Image::make(config('app.url') . '/storage/' . $path);

//        $img->resize(100, null, function ($constraint) {
//            $constraint->aspectRatio();
//        });

        $fileData = [
            'file_name' => $name,
            'mime_type' => $fileArray['src']->extension(),
            'size' => Storage::size('public/' . $path),
        ];

        $media->update($fileData);

        return $media;
    }

    public function destroy(Media $medium) {
        Storage::disk('public')->deleteDirectory('media/' . $medium->id . '/');

        foreach ($medium->tags as $tag) { // TODO возможно стоит перенести на наблюдателя
            $medium->tags()->detach($tag);
        }

        return $medium->delete();
    }

    public function setTitleImage($entity, $mediaId)
    {
        $media = Media::query()->where('id', $mediaId)->first();

        if ($media) {
            return $entity->titleImage()->syncWithPivotValues($media->id, ['type' => Media::TITLE_TYPE]);
        }
    }

    public function setCovers($entity, $coversArray)
    {
        $coversData = [];

        foreach ($coversArray as $cover) {
            if (isset($cover['id'])) {
                $media = Media::query()->find($cover['id']);

                if ($media) {
                    $pivotValues = ['type' => Media::COVER_TYPE];

                    if (isset($cover['sort'])) {
                        $pivotValues['sort'] = $cover['sort'];
                    }

                    $coversData[$media->id] = $pivotValues;
                }
            }
        }

        return $entity->cover()->sync($coversData);
    }

    public function setMediaGroup($entity, $galleryArray)
    {
        $arGalleryIds = [];

        foreach ($galleryArray as $gallery) {
            if (isset($gallery['id'])) {
                $mediaObj = Media::query()->where('id', $gallery['id'])->first();

                if ($mediaObj) {
                    $arGalleryIds[$mediaObj->id] = array(
                        'type' => Media::MEDIA_GROUP,
                        'sort' => intval($gallery['sort']),
                    );
                }
            }
        }

        return $entity->mediaGroup()->sync($arGalleryIds);
    }

    public function getWebp($media)
    {
        if (!$this->isImageMedia($media)) {
            return false;
        }

        $originalPath = "media/$media->id/$media->file_name";
        $webpPath = str_replace($media->mime_type, 'webp', $originalPath);

        /* Проверяем, что файл, который мы собираемся обрабатывать существует */
        if (!Storage::disk('public')->exists($originalPath)) {
            return false;
        }

        if (!Storage::disk('public')->exists($webpPath)) {
            $image = Image::read(storage_path("app/public/$originalPath"));
            $image->encode(new WebpEncoder(80))->save(storage_path("app/public/$webpPath"));
        }

        return config('app.url') . "/storage/" . str_replace('\\', '/', $webpPath);
    }

    public function getResizes($media)
    {
        if (!$this->isImageMedia($media)) {
            return false;
        }

        $originalPath = "media/$media->id/$media->file_name";

        /* Проверяем, что файл, который мы собираемся обрабатывать существуе */
        if (!Storage::disk('public')->exists($originalPath)) {
            return false;
        }

        $resizesList = [
            300 => [],
            500 => [],
            1000 => [],
            1500 => [],
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

    private function isImageMedia($media): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array(strtolower($media->mime_type), $imageExtensions);
    }

}
