<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\MagicLink;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    public function createLink($userId)
    {
        if (!$userId) {
            return ErrorService::message('Не получен ID пользователя');
        }

        $user = User::findOrFail($userId);

        if (!$user) {
            return ErrorService::message('Пользователь не найден');
        }

        $link = MagicLink::generateFor($user);


        return response()->json(['token' => $link->token]);
    }

    public function login($token)
    {
        $link = MagicLink::where('token', $token)->first();

        if (!$link || $link->isExpired()) {
            abort(403, 'Ссылка недействительна или устарела');
        }

        Auth::login($link->user);

        // Можно удалить токен, чтобы нельзя было использовать повторно
        $link->delete();

        // Возвращать пользователя
        return Auth::user();
    }
}
