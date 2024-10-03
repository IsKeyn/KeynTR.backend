<?php

namespace App\Http\Controllers;

use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function getList() {
        $data = Movie::query()->get();
        return MovieResource::collection($data);
    }

    public function getMovie(Request $request, Movie $movie) {
        ViewsLogService::set($request, get_class($movie), $movie->id);
        return MovieResource::make($movie);
    }
}
