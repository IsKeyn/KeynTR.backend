<?php

namespace App\Services;

use App\Models\GamingPlatform;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class PlatformService extends ServiceProvider
{
    public static function attachePlatformToEntity($entity, $gamePlatformName) {
        $platformEntity = self::findOrCreate($gamePlatformName);
        $entity->platform()->sync($platformEntity->id);

        return $platformEntity;
    }

    public static function findOrCreate($gamePlatformName)
    {
        $entity = GamingPlatform::where('name', $gamePlatformName)->first();

        if (!$entity) {
            $entity = GamingPlatform::create(['name' => $gamePlatformName]);
        }

        return $entity;
    }
}
