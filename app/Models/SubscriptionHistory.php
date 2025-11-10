<?php

namespace App\Models;

use App\Enums\SubscriptionHistoryAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    use HasFactory;

    protected $table = 'subscription_history';
    protected $fillable = ['subscription_id', 'action'];

    protected $casts = [
        'action' => SubscriptionHistoryAction::class,
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
