<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SocialAccount;
use App\Models\Subscription;
use App\Models\ReminderSetting;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Account extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = ['full_name', 'email', 'phone', 'avatar', 'role', 'password'];

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
        return [];
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function reminderSettings(): HasOne
    {
        return $this->hasOne(ReminderSetting::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
