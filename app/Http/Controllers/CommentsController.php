<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comments;
use App\Services\CommentService;
use Illuminate\Http\Request;


class CommentsController extends Controller
{
    protected $model = Comments::class;

    public function getList(Request $request) {
        // 1. Получаем список родительских комментариев, делим их по пагинации
        $comments = $this->model::query()
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
            ->where('entity_type', '=', $request->entityType)
            ->where('entity_id','=', $request->entityId)
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
