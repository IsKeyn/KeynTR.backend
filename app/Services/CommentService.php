<?php

namespace App\Services;

use App\Models\Comments;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class CommentService extends ServiceProvider
{
    public static function getAnswers($entityType, $entityId, $commentId)
    {
        return Comments::query()
            ->where('entity_type', '=', $entityType)
            ->where('entity_id','=', $entityId)
            ->where('answer_to','=', $commentId)
            ->get();
    }

    public static function addComment($request, $newComment)
    {
        if ($user = $request->user()) {
            $newComment['created_by'] = $user->id;
        }

        if ($comment = Comments::create($newComment)) {
            UserAgentService::setData($request, $comment);

            return response($comment, Response::HTTP_CREATED);
        }
    }
}
