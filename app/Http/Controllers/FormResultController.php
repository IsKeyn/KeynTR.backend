<?php

namespace App\Http\Controllers;

use App\Models\FormResult;
use Illuminate\Http\Request;

class FormResultController extends Controller
{
    public function set(Request $request)
    {
        return FormResult::create(['data' => $request->form]);
    }
}
