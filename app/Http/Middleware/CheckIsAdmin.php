<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        $testPassed = false;

        if ($user) {
            foreach ($user->roles as $role) {
                if ($role->system_name === 'super_admin') {
                    $testPassed = true;
                }
            }
        }

        if ($testPassed) {
            return $next($request);
        } else {
            session()->flash('warning', 'У вас нет прав администратора');
            return redirect()->route('home');
        }
    }
}
