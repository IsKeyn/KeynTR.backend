<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\User\MagicLink;
use App\Services\ErrorService;
use Illuminate\Support\Facades\Storage;

class MagicLinkService
{
    public static function createLink($userId, $redirectUrl = null)
    {
        if (!$userId) {
            return ErrorService::message('Не получен ID пользователя');
        }

        $user = User::findOrFail($userId);

        if (!$user) {
            return ErrorService::message('Пользователь не найден');
        }

        $link = MagicLink::generateFor($user, $redirectUrl);

        return $link;
    }

    public static function deleteQrFile($fileName)
    {
        $disk = Storage::disk('public');

        try {
            if ($disk->exists('qr/' . $fileName)) {
                $disk->delete('qr/' . $fileName);
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

}
