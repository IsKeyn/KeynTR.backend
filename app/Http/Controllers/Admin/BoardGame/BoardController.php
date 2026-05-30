<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BoardRequest;
use App\Models\BoardGame\Board;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = Board::class;
    private const CACHE_SERVICE = Board::CACHE_SERVICE;
    private const FILTER = Board::FILTER;
    private const DETAIL_RESOURCE = Board::DETAIL_RESOURCE;
    private const LIST_RESOURCE = Board::LIST_RESOURCE;
    private const SERVICE = Board::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            self::MODEL,
            self::CACHE_SERVICE,
            self::FILTER,
            self::LIST_RESOURCE,
            true,
            ['media']
        );
    }

    public function store(BoardRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BoardRequest $request, Board $board)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $board
        );
    }

    public function destroy(Board $board)
    {
        return $this->defaultAdminEntityService->destroy($board);
    }
}
