<?php

namespace App\Models;

use App\Models\BoardGame\BoardGamePlayer;
use App\Models\Traits\ExtendModelTrait;
use App\Models\User\Message;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, ExtendModelTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'public_name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'settings',
        'sort',
        'active',
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
        'active' => 'boolean',
        'is_admin' => 'boolean',
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
        return $this->belongsToMany(Role::class)->withTimestamps();
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

    // Кэш будет жить только пока существует экземпляр User (т.е. один запрос)
    protected array $cachedPermissions = [];

    /**
     * Получает все имена прав пользователя (кэшируется автоматически)
     */
    public function getAllPermissions(): array
    {
        if (!empty($this->cachedPermissions)) {
            return $this->cachedPermissions;
        }

        // Загружаем из БД только при первом вызове
        $this->cachedPermissions = $this->roles()
            ->with('permissions:system_name') // грузим только имя права
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('system_name')
            ->unique()
            ->values()
            ->toArray();

        return $this->cachedPermissions;
    }

    /**
     * Проверка права с использованием кэша
     */
    public function hasPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->getAllPermissions(), true);
    }

    public function bgPlayer(): hasMany
    {
        return $this->hasMany(BoardGamePlayer::class, 'user_id');
    }
}
