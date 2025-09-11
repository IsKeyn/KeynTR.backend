<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserPublicResource;
use App\Models\User;

class UserController extends Controller
{
    public function list()
    {
        return UserPublicResource::collection(User::verified()->get());
    }
}
