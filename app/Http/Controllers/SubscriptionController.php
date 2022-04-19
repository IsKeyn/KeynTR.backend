<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $model = Subscription::class;

    public function add(Request $request) {

       if ($this->model::where('email', '=', $request->payload)->first()) {
           return false;
       } else {
            $arInsert = [
                'email' => $request->payload,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ];

            return $this->model::create($arInsert);
       }
    }
}
