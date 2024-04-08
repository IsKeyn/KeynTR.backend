<?php

namespace App\Http\Controllers;

use App\Models\VotesLog;
use App\Services\UserAgentService;
use Illuminate\Http\Request;

class VotesLogController extends Controller
{
    public function setLike(Request $request) {
        $vote = VotesLog::create([
            'vote_type' => VotesLog::LIKE,
            'vote_value' => 1,
            'entity_type' => $request->entityType,
            'entity_id' => $request->entityId,
            'created_at' => $request->user() ? $request->user()->id : '',
        ]);

        UserAgentService::setData($request, $vote);

        return $vote;
    }
}
