<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\MagicLink;
use App\Services\ErrorService;
use App\Services\User\MagicLinkService;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    public function createLink($userId)
    {
        $link = MagicLinkService::createLink($userId);

        if (isset($link['error'])) {
            return $link['error'];
        }

        return response()->json([
            'token' => $link->token,
            'qr_code' => $link->qr_code,
            'expires_at' => $link->expires_at,
        ]);
    }

    public function login($token)
    {
        $link = MagicLink::where('token', $token)->first();

        if (!$link || $link->isExpired()) {
            abort(403, 'Ссылка недействительна или устарела');
        }

        Auth::login($link->user);

        // Удаляем QR код
        MagicLinkService::deleteQrFile(basename($link->qr_code));

        // Удаляем токен, чтобы нельзя было использовать повторно
        $link->delete();

        // Возвращать пользователя
        return Auth::user();
    }
}
