<?php

namespace App\Services;

use App\Models\Tag;
use App\Services\Entity\EntityService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class TagService extends ServiceProvider
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\Tag',
            'App\Services\Cache\TagCacheService',
            'App\Http\Resources\Admin\Tag\DetailResource',
            $id,
            ['additionalFields'],
            $forceRefresh,
            $withTrashed,
        );
    }

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
