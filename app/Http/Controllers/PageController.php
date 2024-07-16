<?php

namespace App\Http\Controllers;

use App\Http\Resources\PageResource;
use App\Models\Page;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\Request;

class PageController extends Controller {
    public function getList(Request $request)
    {
        dd($request);
    }

    public function getByPath(Request $request)
    {
        $validated = $request->validate([
            'full_path' => 'nullable|string',
        ]);

        if (isset($validated['full_path'])) {
            $page = Page::where('path', $validated['full_path'])->first();

            if ($page) {
                return response()->json(PageResource::make($page))->setStatusCode(Response::HTTP_OK);
            } else {
                return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
            }
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request)
    {
        dd($request);
    }
}
