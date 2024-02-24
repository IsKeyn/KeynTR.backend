<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;
use Illuminate\Auth\Notifications\ResetPassword;

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
                ->subject(__('notification.account_verification_theme'))
                ->markdown('mails.ru.verify_email', ['url' => $url]);
        });
    }
}
