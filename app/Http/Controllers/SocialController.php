<?php

namespace App\Http\Controllers;

use App\Http\Resources\SocialResource;
use App\Models\Social;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function getList() {
        $social = Social::query()->get();
        return SocialResource::collection($social);
    }

    public function getSocial(Request $request, Social $social) {
        return SocialResource::make($social);
    }
}
