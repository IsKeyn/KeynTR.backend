<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function sendLoginResponse(Request $request, $url = null)
    {
        $user = $request->user();

        return $user;

//        foreach ($user->tokens as $token) {
//            dump($token);
//        }

//        $token = $request->user()->createToken('asdasd');

//        dd($user->tokens);
//
//        return ['token' => $token->plainTextToken];

//        dd($user);
//
//        return $user->tokens;

//        foreach ($user->tokens as $token) {
//            //
//        }

//        $this->clearLoginAttempts($request);

//        $token = (string)$this->guard()->getToken();
//        $expiration = $this->guard()->getPayload()->get('exp');
//
//        /** @var User|null $user */
//        $user = $request->user();
//
//        if ($user) {
//            $user->update([
//                'latest_login_at' => now(),
//            ]);
//        }
//
//        return response()->json([
//            'token' => $token,
//            'token_type' => 'bearer',
//            'expires_in' => $expiration - time(),
//            'url' => $url,
//        ]);
    }
}
