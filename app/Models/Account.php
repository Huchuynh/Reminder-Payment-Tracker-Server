<?php

namespace App\Models;

use App\Enums\AccountRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;


class Account extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = ['full_name', 'email', 'phone', 'avatar', 'role', 'password', 'fcm_token'];

    protected $casts = [
        'role' => AccountRole::class,
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    // Các hàm của JWTSubject mà bạn đã có

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            "role" => $this->role
        ];
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function reminderSettings(): HasOne
    {
        return $this->hasOne(ReminderSetting::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
