<?php

namespace App\Services;

use App\Models\Comments;
use App\Services\Cache\CommentCacheService;
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
            $commentCacheService = app(CommentCacheService::class);
            $commentCacheService->clearListCacheByEntity($request->entity_type, $request->entity_id);

            UserAgentService::setData($request, $comment);

            return response($comment, Response::HTTP_CREATED);
        }
    }
}
