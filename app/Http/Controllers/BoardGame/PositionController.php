<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Models\BoardGame\BoardGamePlayerPosition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PositionController extends Controller
{
    protected $model = BoardGamePlayerPosition::class;

    public function add(Request $request)
    {
        $newEntry = $request->validate([
            'position' => 'required',
            'board_game_id' => 'required',
        ]);

        if ($user = $request->user()) {
            $newEntry['user_id'] = $user->id;
            $newEntry['created_by'] = $user->id;

            if ($entry = $this->model::create($newEntry)) {
                return response($entry, Response::HTTP_CREATED);
            }
        }
    }
}
