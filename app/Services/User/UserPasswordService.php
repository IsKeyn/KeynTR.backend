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

    public function generatePassword($length = 8)
    {
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';

        // Гарантируем минимум 1 цифру и 1 букву
        $password = [
            $letters[rand(0, strlen($letters) - 1)],
            $digits[rand(0, strlen($digits) - 1)]
        ];

        // Добавляем остальные символы
        $allChars = $letters . $digits;
        for ($i = 2; $i < $length; $i++) {
            $password[] = $allChars[rand(0, strlen($allChars) - 1)];
        }

        // Перемешиваем
        shuffle($password);

        return implode('', $password);
    }
}
