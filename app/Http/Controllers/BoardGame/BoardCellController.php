<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BoardCellReviewGetRequest;
use App\Http\Requests\BoardGame\BoardCellReviewSetRequest;
use App\Http\Resources\BoardGame\Board\Cell\ReviewResource;
use App\Services\BoardGame\BoardCellService;
use Illuminate\Http\Request;

class BoardCellController extends Controller
{
    public function __construct(
        private BoardCellService $boardCellService
    ) {}

    /**
     * @param BoardCellReviewGetRequest $request
     */
    public function getCurrentPlayerReview(BoardCellReviewGetRequest $request)
    {
        $player = $request->attributes->get('player');
        $boardGame = $request->attributes->get('boardGame');

        $newReview = $this->boardCellService->getCurrentPlayerReview(
            $player,
            $boardGame,
            $request->board_position_effects_id,
        );

        if (!$newReview) {
            return response()->json(null); // или 404, зависит от вашей логики
        }

        return ReviewResource::make($newReview);
    }

    /**
     * @param BoardCellReviewSetRequest $request
     * @return ReviewResource
     */
    public function setReview(BoardCellReviewSetRequest $request): ReviewResource
    {
        $user = $request->attributes->get('user');
        $player = $request->attributes->get('player');
        $boardGame = $request->attributes->get('boardGame');

        $data = $request->validated();

        $newReview = $this->boardCellService->setReview(
            $user,
            $player,
            $boardGame,
            $data,
            $request,
        );

        $newReview->load(['comment', 'comment.user']);

        return ReviewResource::make($newReview);
    }

    public function getCurrentEventReview(Request $request)
    {
        $boardGame = $request->attributes->get('boardGame');

        $reviews = $this->boardCellService->getCurrentEventReview($boardGame->id, $request->id, $request->page, $request->perPage);

        return ReviewResource::collection($reviews);
    }

    public function getOtherEventReview(Request $request)
    {
        $boardGame = $request->attributes->get('boardGame');

        $reviews = $this->boardCellService->getOtherEventReview($boardGame->id, $request->id, $request->page, $request->perPage);

        return ReviewResource::collection($reviews);
    }
}
