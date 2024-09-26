<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
//use Intervention\Image\Facades\Image;

class AdminMediaPagesController extends Controller
{
    public function index(Request $request) {
        $mediaQuery = Media::query();

        $result = $request->perPage ? $mediaQuery->paginate($request->perPage) : $mediaQuery->get();
        return MediaResource::collection($result);
    }

    public function store(Request $request) {
        $fileArray = [
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'src' => $request->file('src'),
            'tags' => $request->tags,
        ];

        return $this->addMedia($fileArray, $request->user());
    }

    public function multiStore(Request $request) {
        $result = array();

        foreach ($request->multiFiles as $key => $fileArray) {
            $result[] = $this->addMedia($fileArray, $request->user());
        }

        return $result;
    }

    // Вынести в сервис
    protected function findOrCreateTag($tagName)
    {
        $tag = Tag::where('name', $tagName)->first();

        if (!$tag) {
            $tag = Tag::create(['name' => $tagName]);
        }

        return $tag;
    }

    private function addMedia($fileArray, $user) {
        $fileData = [
            'name' => $fileArray['name'],
            'description' => $fileArray['description'],
            'type' => $fileArray['type'],
            'created_by' => $user->id,
        ];

        $media = Media::create($fileData);

        if (isset($fileArray['tags'])) {
            foreach ($fileArray['tags'] as $tagName) {
                $tag = $this->findOrCreateTag($tagName);
                $media->tags()->save($tag);
            }
        }

        $name = $fileArray['src']->getClientOriginalName();
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

    public function edit(Media $medium)
    {
        return MediaResource::make($medium);
    }

    public function destroy(Media $medium) {
//        Storage::disk('public')->delete('media/' . $medium->id . '/' . $medium->file_name);
        Storage::disk('public')->deleteDirectory('media/' . $medium->id . '/');

        foreach ($medium->tags as $tag) { // TODO возможно стоит перенести на наблюдателя
            $medium->tags()->detach($tag);
        }

        return $medium->delete();
    }

    public function update(Request $request, Media $medium) {
        $fileData = [
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
        ];

        if ($request->file('src')) {
            Storage::disk('public')->delete('media/' . $medium->id . '/' . $medium->file_name);

            $name = $request->file('src')->hashName();
            $path = $request->file('src')->storeAs(
                'media/' . $medium->id,
                $name,
                'public'
            );

            $fileData['file_name'] = $name;
            $fileData['mime_type'] = $request->file('src')->extension();
            $fileData['size'] = Storage::size('public/' . $path);
        }

        // Работа с тегами
        if ($request->tags) {
            $arCurrentTags = [];

            // Текущие теги элемента
            foreach ($medium->tags as $tag) {
                if ($tag->name) {
                    if (!in_array($tag->name, $request->tags)) {
                        $medium->tags()->detach($tag);
                    } else {
                        $arCurrentTags[] = $tag->name;
                    }
                }
            }

            foreach ($request->tags as $tagName) {
                $tag = $this->findOrCreateTag($tagName);

                if (!in_array($tagName, $arCurrentTags)) {
                    $medium->tags()->save($tag);
                }
            }
        } else {
            foreach ($medium->tags as $tag) {
                $medium->tags()->detach($tag);
            }
        }

        $medium->update($fileData);

        return $medium;
    }
}
