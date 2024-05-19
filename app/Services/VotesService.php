<?php

namespace App\Services;

use App\Models\VotesCount;
use App\Models\VotesLog;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class VotesService extends ServiceProvider
{
    public static function calcVotes($entity, $entityId, $type)
    {
        $votes = VotesLog::query()
            ->where('entity_type', $entity)
            ->where('entity_id', $entityId)
            ->where('was_counted', false)
            ->where('vote_type', $type)
            ->get();

        $value = 0;

        $votesCount = VotesCount::query()
            ->where('entity_type', $entity)
            ->where('entity_id', $entityId)
            ->where('vote_type', $type)
            ->first();

        if ($votesCount) {
            $value = intval($votesCount->value);
        }

        foreach ($votes as $vote) {
            if ($type === VotesLog::LIKE) {
                $value = $value + $vote->vote_value;
                $vote->was_counted = true;
                $vote->save();
            }
        }

        return VotesCount::updateOrCreate(
            [
                'vote_type' => $type,
                'entity_type' => $entity,
                'entity_id' => $entityId,
            ],
            [
                'vote_type' => $type,
                'value' => $value,
                'entity_type' => $entity,
                'entity_id' => $entityId,
            ]
        );
    }

    public static function alreadyVoted($entityType, $entityId, $type, $userId)
    {
        $alreadyVoted = false;

        if ($userId) {
            $query = VotesLog::query()
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->where('vote_type', $type)
                ->where('created_by', $userId)
                ->first();

            if ($query) {
                $alreadyVoted = true;
            }
        }

        return $alreadyVoted;
    }
}
