<?php

namespace App\Services;

use App\Models\Date;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class DateService extends ServiceProvider
{
    public static function attacheDateToEntity($entity, $date) {
        $dateEntity = self::findOrCreate($date);
        $entity->dates()->sync($dateEntity->id);

        return $dateEntity;
    }

    public static function findOrCreate($date)
    {
        $dateEntity = Date::where('date', $date)->first();

        if (!$dateEntity) {
            $dateEntity = Date::create(['date' => $date]);
        }

        return $dateEntity;
    }
}
