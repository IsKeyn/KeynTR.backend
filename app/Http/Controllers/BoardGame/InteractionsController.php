<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Services\BoardGame\InteractionsService;
use Illuminate\Http\Request;

class InteractionsController extends Controller
{
    public function action(Request $request, InteractionsService $interactionsService) {
        return $interactionsService->action($request);
    }
}
