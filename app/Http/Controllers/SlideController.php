<?php

namespace App\Http\Controllers;

use App\Http\Resources\SlideResource;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    public function getSlideByType(Request $request) {
        $slides = Slide::query()->where('type', $request->type)->get();

        return SlideResource::collection($slides);
    }
}
