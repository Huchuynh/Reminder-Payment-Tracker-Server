<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Policies\SubscriptionPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(SubscriptionPolicy::class)]
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id', 'service_id', 'start_date', 'end_date', 'status', 'plan', 'notes',
        'alert_thresholds', 'reminder_frequency', 'reminder_channels', 'last_reminded_at',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'reminder_channels' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_reminded_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
