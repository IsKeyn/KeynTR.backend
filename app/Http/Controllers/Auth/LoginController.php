<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\UserActionLogService;
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
                'message' => __('user_action_log.success_auth'),
                'created_by' => $user->id
            ];

            UserActionLogService::set($request, $UserActionLogParams);

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $userActionLogParams = [
            'message' => [
                'message' => __('user_action_log.auth_fail'),
                'email' => $request->email,
                'password' => $request->password,
            ]
        ];

        if ($user) {
            $userActionLogParams['created_by'] = $user->id;
        }

        UserActionLogService::set($request, $userActionLogParams);

        // TODO проверять, что конкретно не подошло пароль или логин
        return $this->sendFailedLoginResponse($request);
    }

    protected function sendLoginResponse(Request $request, $url = null)
    {
        $user = $request->user();

        if ($user) {
            //$user->tokens()->delete(); // TODO несколько токенов для авторизации на нескольких устройствах
            // TODO удалять старые токены через определённое время, например через год, комманда

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

    public function logout(Request $request)
    {
        auth()->guard('web')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Удаление текущего токена пользователя
        $request->user()->currentAccessToken()->delete();

        // Запись логов
        $userActionLogParams = [
            'message' => [
                'message' => __('user_action_log.logout'),
            ]
        ];

        if ($request->user()) {
            $userActionLogParams['created_by'] = $request->user()->id;
        }

        UserActionLogService::set($request, $userActionLogParams);

        return true;
    }
}
