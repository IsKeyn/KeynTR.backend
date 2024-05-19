<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class TagService extends ServiceProvider
{
    public static function attacheTagsToEntity($entity, $tags) {
        $arTag = [];

        foreach ($tags as $tagName) {
            $tag = self::findOrCreateTag($tagName);
            $arTag[] = $tag->id;
        }

        $entity->tags()->sync($arTag);
    }

    public static function findOrCreateTag($tagName)
    {
        $tag = Tag::where('name', $tagName)->first();

        if (!$tag) {
            $tag = Tag::create(['name' => $tagName]);
        }

        return $tag;
    }
}
