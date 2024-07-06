<?php

namespace App\Services;

use App\Models\Link;

class LinkService
{
    public static function set($entity, $data) // TODO сделать общую функцию, меняется только модель и ключ, причем модель === ключ
    {
        $arItemsIds = [];

        foreach ($data as $item) {
            if (isset($item['url'])) {
                $arLink = [
                    'name' => $item['name'],
                    'url' => $item['url'],
                ];

                $linkEntity = Link::query()->where('url', $item['url'])->first();

                if ($linkEntity) {
                    $linkEntity->update($arLink);
                    $arItemsIds[] = $linkEntity->id;
                } else {
                    $newLink = Link::create($arLink);
                    $arItemsIds[] = $newLink->id;
                }
            }
        }

        return $entity->link()->sync($arItemsIds);
    }
}
