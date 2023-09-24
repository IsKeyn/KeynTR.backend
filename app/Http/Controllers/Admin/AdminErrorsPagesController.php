<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Error;
use Illuminate\Http\Request;

class AdminErrorsPagesController extends Controller
{
    public function index() {
        $errors = Error::all();

        return view('admin.errors.index', compact('errors'));
    }
}
