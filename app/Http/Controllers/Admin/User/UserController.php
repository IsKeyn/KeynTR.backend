<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function fullLogout($userId)
    {
        $user = User::find($userId);
        $user->update(['remember_token' => Str::random(60)]);
        $this->logoutUserById($userId);

        return true;
    }

    private function logoutUserById($userId)
    {
        // TODO Добавить проверку, какое значение стоит в SESSION_DRIVER и использовать разные методы разлогинивания
        $deleted = 0;
        $sessionFiles = glob(storage_path('framework/sessions/*'));

        foreach ($sessionFiles as $file) {
            $content = @file_get_contents($file);
            if ($content === false) continue;

            // два безопасных паттерна: сериализованный ключ s:N:"login_...";i:ID;
            // и вариант с "login_..." без s:\d+ префикса
            $patterns = [
                '/s:\d+:"login_[^"]*";i:' . (int)$userId . ';/',
                '/"login_[^"]*";i:' . (int)$userId . ';/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    @unlink($file); // удаляем файл сессии
                    $deleted++;
                    break;
                }
            }
        }

        return $deleted; // возвращает сколько файлов удалено
    }
}
