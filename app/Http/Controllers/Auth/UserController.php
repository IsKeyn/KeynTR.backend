<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function authUser(Request $request) {
        $user = $request->user();

        return $user;
    }
}
