<?php
namespace App\Models\User;

use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MagicLink extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'qr_code',
    ];

    protected $dates = ['expires_at'];

    public static function generateFor($user, $redirectUrl = null)
    {
        $qrCodeService = new QrCodeService();

        $token = Str::random(64);

        $publicUrl = config('publicApp.public_url') . '/auth/autologin/' . $token . '?redirectUrl=' . $redirectUrl;
        $qrCode = $qrCodeService->makeSvgUrl($publicUrl);

        $magicLink = self::create([
            'user_id' => $user->id,
            'token' => $token,
            'qr_code' => $qrCode,
            'expires_at' => now()->addMinutes(10), // срок действия 10 минут
        ]);

        return $magicLink;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPublicUrlAttribute()
    {
        return config('publicApp.public_url') . '/auth/autologin/' . $this->token;
    }

}
