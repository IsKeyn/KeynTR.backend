<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActionLog;
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

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($request->email) {
            $user = User::query()->where('email', $request->email)->first();
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            $UserActionLogParams = [
                'message' => 'Успешная авторизация',
                'created_by' => $user->id
            ];

            UserActionLog::create($UserActionLogParams);

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        $UserActionLogParams = [];

        $UserActionLogParams['message'] = [
            'message' => 'Провал авторизации',
            'email' => $request->email,
            'password' => $request->password,
        ];

        if ($user) {
            $UserActionLogParams['created_by'] = $user->id;
        }

        UserActionLog::create($UserActionLogParams);

        // TODO проверять, что конкретно не подошло пароль или логин
        return $this->sendFailedLoginResponse($request);
    }

    protected function sendLoginResponse(Request $request, $url = null)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete(); // TODO несколько токенов для авторизации на нескольких устройствах
            $token = $user->createToken('api')->plainTextToken;

            // Сделать таблицу действий пользователя
            //            $user->update([
//                'latest_login_at' => now(),
//            ]);
//        }

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires' => time() + 360 * 24 * 60 * 60,
//                'url' => $url,
            ]);
        }
    }
}
