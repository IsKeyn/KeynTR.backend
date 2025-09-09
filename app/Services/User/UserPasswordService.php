<?php

namespace App\Services\User;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPasswordService
{
    const SUCCESS_CHANGE = 'passwords.change';
    const PASSWORD_IS_INCORRECT = 'auth.password';

    public function changePassword($user, $data)
    {
        if (Hash::check($data['currentPassword'], $user->password)) {
            /* TODO Добавить проверку на сложность пароля и возвращать ошибку, если не соотвествует сложности */

            $user->forceFill([
                'password' => Hash::make($data['password'])
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));

            $status = self::SUCCESS_CHANGE;
        } else {
            $status = self::PASSWORD_IS_INCORRECT;
        }

        return [
            'status' => __($status),
            'status_code' => $status,
        ];
    }
}
