<?php

namespace App\Http\Controllers;

use App\Http\Resources\Comments\CommentResource;
use App\Models\Comments;
use App\Services\Cache\CommentCacheService;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommentsController extends Controller
{
    public function getList(Request $request) {
        $perPage = $request->perPage ? $request->perPage : 10;
        $cacheKey = CommentCacheService::LIST_PREFIX . '_' . $request->entityType . '_' . $request->entityId . '_' . $request->page . '_' . $perPage;

        return Cache::remember($cacheKey, CommentCacheService::TIME, function () use ($request) {
            // 1. Получаем список родительских комментариев, делим их по пагинации
            $comments = Comments::query()
                ->with([
                    'user',
                    'user.avatar',
                    'bgPlayerGame.boardGame',
                ])
                ->where('entity_type', '=', $request->entityType)
                ->where('entity_id', '=', $request->entityId)
                ->where('answer_to', '=', null)
                ->orderBy('created_at', 'desc')
                ->paginate($request->perPage ? $request->perPage : 10);

            // 2. Формируем массив из ID полученных комментариев
            $arCommentsIds = [];

            foreach ($comments as $comment) {
                $arCommentsIds[] = $comment['id'];
            }

            // 3. Получаем все ответы для комментариев с ID из массива полученного ранее, используем для этого значение first_parent (родительский комментарий)
            $answers = Comments::query()
                ->with([
                    'user',
                    'user.avatar',
                    'bgPlayerGame.boardGame',
                ])
                ->where('entity_type', '=', $request->entityType)
                ->where('entity_id', '=', $request->entityId)
                ->whereIn('first_parent', $arCommentsIds)
                ->get();

            // 4. Формируем массив с ответами, сгруппированными по ID родительского комментария
            $preparedAnswers = [];
            foreach ($answers as $answer) {
                $preparedAnswers[$answer->answer_to][] = $answer;
            }

            // 5. Связываем родительские комметарии с дочерними
            foreach ($comments as &$comment) {
                $comment['answers'] = $this->answerWithAnswers($preparedAnswers, $comment->id);
            }

            return CommentResource::collection($comments);
        });
    }

    private function answerWithAnswers($preparedAnswers, $commentId) {
        $returnData = [];

        if (isset($preparedAnswers[$commentId])) {
            foreach ($preparedAnswers[$commentId] as $id => $comment) {
                $returnData[$id] = $comment;
                unset($preparedAnswers[$commentId]);
                $returnData[$id]['answers'] = $this->answerWithAnswers($preparedAnswers, $comment->id);
            }
        }

        return $returnData;
    }

    public function add(Request $request) {
        $newComment = $request->validate([
            'name' => 'sometimes',
            'email' => 'sometimes|email',
            'message' => 'required|min:2',
            'entity_type' => 'required',
            'entity_id' => 'required',
            'first_parent' => 'sometimes',
            'answer_to' => 'sometimes',
        ]);

        return CommentService::addComment($request, $newComment);
    }
}
