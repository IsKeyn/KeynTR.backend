<?php

namespace App\Models;

use App\Models\User\Message;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'array',
    ];

    public function isAdmin() {
        return $this->is_admin === 1;
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('email_verified_at', '!=', null);
    }

    /**
     * Отправить пользователю уведомление о сбросе пароля
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Роли, принадлежащие пользователю.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function avatar()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function additionalFields()
    {
        return $this->morphMany(AdditionalField::class, 'entity');
    }

    // Сообщения, где пользователь является отправителем
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'created_by');
    }

    // Сообщения, где пользователь является получателем
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient');
    }

    // Все сообщения, связанные с пользователем (как отправителем, так и получателем)
    public function allMessages()
    {
        return Message::where(function($query) {
            $query->where('created_by', $this->id)
                ->orWhere('recipient', $this->id);
        });
    }

    // Получить все сообщения, где пользователь является участником
    public function messages()
    {
        return $this->allMessages();
    }
}
