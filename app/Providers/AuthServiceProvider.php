<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

//        ResetPassword::createUrlUsing(function ($user, string $token) {
//            return config('publicApp.public_url') . '/reset-password?token=' . $token . '&email=' . $user->email;
//        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            if (config('publicApp.public_url') && config('app.url')) {
                $url = Str::replace('api/', '', Str::replace(config('app.url'), config('publicApp.public_url'), $url));
            }

            return (new MailMessage)
//                ->subject(__('notification.account_verification_theme'))
                ->subject('Подтверждение учетной записи на сайте ' . config('publicApp.public_url'))
                ->markdown('mails.ru.verify_email', ['url' => $url]);
        });

        // Этот замыкание вызывается ДО любой проверки Gate
        Gate::before(function ($user, $ability) {
            // Если пользователь не авторизован — пропускаем проверку
            if (!$user) {
                return null;
            }

            // Если есть право 'admin.super' — разрешаем всё
            if ($user->hasPermission('admin.super')) {
                return true;
            }

            // Проверяем через нашу модель (сработает кэш)
            if ($user->hasPermission($ability)) {
                return true; // Разрешаем доступ сразу
            }

            // Возвращаем null, чтобы Laravel проверил другие Gate::define() или политики
            return null;
        });
    }
}
