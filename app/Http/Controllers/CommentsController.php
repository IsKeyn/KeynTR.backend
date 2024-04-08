<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comments;
use App\Services\UserAgentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class CommentsController extends Controller
{
    protected $model = Comments::class;

    public function getList(Request $request) {

        $comments = $this->model::query()
            ->where('entity_type', '=', $request->entityType)
            ->where('entity_id', '=', $request->entityId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->perPage ? $request->perPage : 10);


        return CommentResource::collection($comments);
    }

    public function add(Request $request) {
        $newComment = $request->validate([
            'name' => 'sometimes',
            'email' => 'sometimes|email',
            'message' => 'required|min:2',
            'entity_type' => 'required',
            'entity_id' => 'required',
        ]);

        if ($user = $request->user()) {
            $newComment['created_by'] = $user->id;
        }

        if ($comment = $this->model::create($newComment)) {
            UserAgentService::setData($request, $comment);

            return response($comment, Response::HTTP_CREATED);
        }
    }
}
