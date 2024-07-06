<?php

namespace App\Services;

use App\Models\Block;

class BlockService
{
    public static function set($entity, $data)
    {
        $arItemsIds = [];

        foreach ($data as $item) {
            if (isset($item['name']) && isset($item['structure'])) {
                $arItem = [
                    'name' => $item['name'],
                    'structure' => $item['structure'],
                ];

                if (isset($item['id'])) {
                    $itemEntity = Block::query()->where('id', $item['id'])->first();
                    $itemEntity->update($arItem);
                    $arItemsIds[] = $itemEntity->id;
                } else {
                    $itemEntity = Block::create($arItem);
                    $arItemsIds[] = $itemEntity->id;
                }
            }
        }

        return $entity->blocks()->sync($arItemsIds);
    }
}
