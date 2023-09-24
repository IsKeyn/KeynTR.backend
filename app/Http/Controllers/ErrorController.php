<?php

namespace App\Http\Controllers;

use App\Http\Resources\ErrorResource;
use App\Models\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ErrorController extends Controller
{
    protected $model = Error::class;

    public function set(Request $request) {

        /*
         * Типы ошибок для type
         * public - ошибки из публичной части сайта
         */

        $arInsert = [
            'message' => $request->message,
            'type' => 'public',
            'from' => $request->from,
        ];

        return ErrorResource::make($this->model::create($arInsert));
    }

    public function get() {
        return ErrorResource::collection(Cache::remember('errors', 60*60*24, function () {
           return $this->model::all();
        }));

    }
}
