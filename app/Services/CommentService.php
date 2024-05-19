<?php

namespace App\Services;

use App\Models\Comments;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class CommentService extends ServiceProvider
{
    public static function getAnswers($entityType, $entityId, $commentId) {
        return Comments::query()
            ->where('entity_type', '=', $entityType)
            ->where('entity_id','=', $entityId)
            ->where('answer_to','=', $commentId)
            ->get();
    }
}
