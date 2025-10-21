<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;

class SocialAccount extends Model
{
    protected $fillable = [
        'account_id',
        'provider',
        'provider_id',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
