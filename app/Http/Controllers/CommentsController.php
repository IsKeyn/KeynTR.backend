<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comments;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    protected $model = Comments::class;

    public function getList(Request $request) {

        $comments = $this->model::
            where('entity_type', '=', $request->payload['entity'])
            ->where('entity_id', '=', $request->payload['entityId'])
            ->get();

        $returnData = array();

        foreach ($comments as $comment) {
            $returnData[] = CommentResource::make($comment);
        }

        return $returnData;
    }

    public function add(Request $request) {

        $arInsert = [
            'author_name'   => $request->payload['name'],
            'email'         => $request->payload['email'],
            'message'       => $request->payload['message'],
            'entity_type'   => $request->payload['entity'],
            'entity_id'     => $request->payload['entityId'],
        ];

        return $this->model::create($arInsert);
    }
}
