<?php

namespace App\Http\Controllers;

use App\Http\Resources\VotesCountResource;
use App\Models\VotesCount;
use App\Models\VotesLog;
use App\Services\UserAgentService;
use App\Services\VotesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VotesLogController extends Controller
{
    public function setLike(Request $request)
    {
        $attributes = $request->validate([
            'entityType' => 'required',
            'entityId' => 'required|int',
        ]);

        $user = $request->user();
        $vote = $this->getUserVote($request);

        if (!$vote) {
            $vote = VotesLog::create([
                'vote_type' => VotesLog::LIKE,
                'vote_value' => 1,
                'entity_type' => $attributes['entityType'],
                'entity_id' => $attributes['entityId'],
                'created_by' => $user ? $user->id : null,
            ]);

            UserAgentService::setData($request, $vote);
            $votesCount = VotesService::calcVotes($attributes['entityType'], $attributes['entityId'], VotesLog::LIKE);

            return response()->json(VotesCountResource::make($votesCount))->setStatusCode(Response::HTTP_CREATED);
        } else {
            return response(
                [
                    'message' => __('user_action.already_voted'),
                ],
                Response::HTTP_CREATED
            );
        }
    }

    public function unsetLike(Request $request)
    {
        $attributes = $request->validate([
            'entityType' => 'required',
            'entityId' => 'required|int',
        ]);

        $user = $request->user();
        $vote = $this->getUserVote($request);

        if ($vote) {
            if ($vote->was_counted) {
                $vote->userAgentData->each->delete();
                $vote->delete();

                $votesCount = VotesCount::query()
                    ->where('entity_type', $attributes['entityType'])
                    ->where('entity_id', $attributes['entityId'])
                    ->where('vote_type', VotesLog::LIKE)
                    ->first();

                $votesCount->value = $votesCount->value - 1;
                $votesCount->save();
            } else {
                $vote->userAgentData->delete();
                $vote->delete();
                $votesCount = VotesService::calcVotes($attributes['entityType'], $attributes['entityId'], VotesLog::LIKE);
            }

            return response()->json(VotesCountResource::make($votesCount))->setStatusCode(Response::HTTP_OK);
//            return response(
//                [
//                    'message' => __('user_action.vote_withdrawn'),
//
//                ],
//                Response::HTTP_OK
//            );
        }

            return response(
                [
                    'message' => __('user_action.vote_dont_found'),

                ],
                Response::HTTP_OK
            );
    }

    private function getUserVote(Request $request)
    {
        $user = $request->user();

        $query = VotesLog::query()
            ->where('entity_type', $request->entityType)
            ->where('entity_id', $request->entityId)
            ->where('vote_type', VotesLog::LIKE);

        if ($user) {
            $query->where('created_by', $user->id);
        } else {
            $query->where('created_by', null);
            $query->whereHas('userAgentData', function ($q) use ($request) {
                $q->where('ip', $request->ip());
            });
        }

        return $query->orderby('id', 'desc')->first();
    }
}
