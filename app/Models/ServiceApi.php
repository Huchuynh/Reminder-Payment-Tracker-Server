<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceApi extends Model
{
    protected $fillable = ['service_id', 'base_url', 'method', 'token'];

    public function services(): BelongsTo {
        return $this->belongsTo(Service::class);
    }
}
