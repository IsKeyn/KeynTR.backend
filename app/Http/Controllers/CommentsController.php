<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    protected $model = Comments::class;

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
