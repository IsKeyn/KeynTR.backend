<?php

namespace App\Services;

use App\Models\ViewsCount;
use App\Models\ViewsLog;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class ViewsService extends ServiceProvider
{
    public static function calcAllVotes()
    {
        $viewsLogs = ViewsLog::query()
            ->where('was_counted', false)
            ->get();

        $viewsCounts = [];

        foreach ($viewsLogs as $views) {
            if ($views->entity_type) {
                if (!isset($viewsCounts[$views->entity_type])) {
                    $viewsCounts[$views->entity_type] = [];
                }
                if (isset($viewsCount[$views->entity_type][$views->entity_id])) {
                    $viewsCounts[$views->entity_type][$views->entity_id]++;
                } else {
                    $viewsCounts[$views->entity_type][$views->entity_id] = 1;
                }

                $views->was_counted = true;
                $views->save();
            }
        }

        foreach ($viewsCounts as $entity => $ids) {
            foreach ($ids as $id => $count) {
                $viewsCount = ViewsCount::query()->where('entity_type', $entity)->where('entity_id', $id)->first();

                $value = $viewsCount ? $viewsCount->value + $count : $count;

                ViewsCount::updateOrCreate(
                    [
                        'entity_type' => $entity,
                        'entity_id' => $id,
                    ],
                    [
                        'value' => $value,
                        'entity_type' => $entity,
                        'entity_id' => $id,
                    ]
                );
            }
        }
    }

    public static function calcVotes($entity, $entityId, $type)
    {
        $views = ViewsLog::query()
            ->where('entity_type', $entity)
            ->where('entity_id', $entityId)
            ->where('was_counted', false)
            ->where('vote_type', $type)
            ->get();

        $value = 0;
    }
}
