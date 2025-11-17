<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use HasFactory;

    protected $fillable = ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'sender_id'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sender_id');
    }
}
