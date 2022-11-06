<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class YouTubePageController extends Controller
{
    public function index() {
        return view('admin.youtube.index');
    }
}
